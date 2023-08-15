<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Totoro1302\PhpWebsocketClient\Enum\CloseStatusCode;
use Totoro1302\PhpWebsocketClient\Enum\Opcode;
use Totoro1302\PhpWebsocketClient\Exception\ClientException;
use Totoro1302\PhpWebsocketClient\Service\Frame\Reader;
use Totoro1302\PhpWebsocketClient\Service\Frame\Writer;
use Totoro1302\PhpWebsocketClient\Service\Handshake\{HeadersBuilder, HeadersValidator, KeyGenerator};
use Totoro1302\PhpWebsocketClient\VO\Frame;

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const STATE_CLOSED = -1;
    private const STATE_STARTING = 0;
    private const STATE_CONNECTED = 1;
    private const STATE_TERMINATING = 2;
    private const AWAIT_HEARTBEAT = 60;
    private const PING_DATA = 'Are you alive ?';
    private $resource;
    private int $currentState = self::STATE_CLOSED;
    private int $lastCheckTs;

    public function __construct(
        private readonly ClientConfigInterface $clientConfig,
        private readonly UriFactoryInterface $uriFactory,
        private readonly KeyGenerator $keyGenerator,
        private readonly HeadersValidator $headersValidator,
        private readonly HeadersBuilder $headersBuilder,
        private readonly Reader $reader,
        private readonly Writer $writer
    ) {
    }

    public function __destruct()
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
        $this->logger->info('Socket closed successfully');
    }

    public function connect(): void
    {
        $this->currentState = self::STATE_STARTING;
        $uri = $this->uriFactory->createUri($this->clientConfig->getUri());

        $this->open(
            $uri,
            $this->clientConfig->getConnectionTimeout()
        );

        $clientKey = $this->keyGenerator->generate();
        $clientHeaders = $this->headersBuilder->build(
            $uri,
            $clientKey
        );

        $this->write($clientHeaders);

        $serverHeaders = $this->read();

        $this->headersValidator->validate($clientKey, $serverHeaders);

        $this->registerSignalHandler();
        $this->currentState = self::STATE_CONNECTED;
        $this->lastCheckTs = time();
    }

    public function pull(): string
    {
        $payload = '';

        while ($this->isRunning()) {
            $frame = $this->reader->read($this->resource);
            $this->logger->info('Pull {opcode} frame', ['opcode' => $frame->getOpcode()->name]);

            switch ($frame->getOpcode()) {
                case Opcode::Ping:
                    $this->pong($frame->getPayload());
                    break;
                case Opcode::Pong:
                    if ($frame->getPayload() === self::PING_DATA) {
                        $this->currentState = self::STATE_CONNECTED;
                        $this->lastCheckTs = time();
                    }
                    break;
                case Opcode::Close:
                    $this->close($frame->getPayload());
                    $this->currentState = self::STATE_CLOSED;
                    break;
                case Opcode::Text:
                case Opcode::Binary:
                case Opcode::Continuation:
                    $payload .= $frame->getPayload();
                    if ($frame->isFinal()) {
                        break 2;
                    } else {
                        break;
                    }
            }
        }

        return $payload;
    }

    public function push(string $payload, Opcode $opcode = Opcode::Text, bool $isMasked = true): void
    {
        if (strlen($payload) < PHP_INT_MAX) {
            $frameCollection = [new Frame(true, $opcode, $payload)];
        } else {
            $payloadExploded = str_split($payload, 4096);

            $firstPayload = array_shift($payloadExploded);
            $firstFrame = new Frame(false, $opcode, $firstPayload);

            $finalPayload = array_pop($payloadExploded);
            $finalFrame = new Frame(true, Opcode::Continuation, $finalPayload);

            $frameCollection = [
                $firstFrame,
                ...array_map(fn($payloadChunk) => new Frame(false, $opcode, $payloadChunk), $payloadExploded),
                $finalFrame
            ];
        }

        $this->writer->write($this->resource, $frameCollection, $isMasked);
        $this->logger->info('Push {opcode} frame', ['opcode' => $opcode->name]);
    }

    public function ping(string $payload): void
    {
        $this->push($payload, Opcode::Ping);
    }

    public function pong(string $payload): void
    {
        $this->push($payload, Opcode::Pong);
    }

    public function close(string $payload): void
    {
        $this->push($payload, Opcode::Close);
    }

    public function isRunning(bool $checkLiveness = true): bool
    {
        if ($checkLiveness) {
            $this->checkHeartbeat();
        }

        return $this->currentState > self::STATE_STARTING;
    }

    public function checkHeartbeat(): void
    {
        if ((time() - $this->lastCheckTs) > self::AWAIT_HEARTBEAT) {
            if ($this->currentState === self::STATE_TERMINATING) {
                $this->close(pack('n', CloseStatusCode::Normal->value));
                $this->currentState = self::STATE_CLOSED;
            } else {
                $this->ping(self::PING_DATA);
                $this->currentState = self::STATE_TERMINATING;
                $this->lastCheckTs = time();
            }
        }
    }

    private function open(UriInterface $uri, int $connectionTimeout): void
    {
        $this->logger->info('Opening socket connection');
        $connectionUri = $this->createConnectionUri($uri);
        // Add persistent flag
        $flags = STREAM_CLIENT_CONNECT;
        if ($this->clientConfig->isPersistent()) {
            $flags |= STREAM_CLIENT_PERSISTENT;
        }

        $errorCode = $errorMessage = null;
        $this->resource = stream_socket_client(
            $connectionUri,
            $errorCode,
            $errorMessage,
            (float)$connectionTimeout,
            $flags,
            stream_context_create()
        );

        if ($this->resource === false || !is_resource($this->resource)) {
            $this->logger->warning('Failed to open connection to {connection_uri}', [
                'connection_uri' => $connectionUri,
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'connection_timeout' => $connectionTimeout,
                'flags' => $flags
            ]);
            throw new ClientException("Unable to open stream socket connection");
        }
        $this->logger->info(
            'Successfully open socket connection to {connection_uri}',
            ['connection_uri' => $connectionUri]
        );
    }

    private function signalHandler(int $signal): void
    {
        $this->logger->debug('Signal handled', [
            'signal' => $signal
        ]);

        $this->close(pack('n', CloseStatusCode::Normal->value));
        $this->currentState = self::STATE_CLOSED;
    }

    private function registerSignalHandler(): void
    {
        pcntl_signal(SIGTERM, $this->signalHandler(...));
        pcntl_signal(SIGINT, $this->signalHandler(...));
        pcntl_signal(SIGQUIT, $this->signalHandler(...));
    }

    private function createConnectionUri(UriInterface $uri): string
    {
        [$scheme, $port] = $uri->getScheme() === 'wss' ? ['ssl', 443] : ['tcp', 80];

        $connectionUri = $uri
            ->withScheme($scheme)
            ->withPort($uri->getPort() ?? $port)
            ->withPath('')
            ->withQuery('')
            ->withFragment('')
            ->withUserInfo('');

        return (string)$connectionUri;
    }

    private function write(string $data): int
    {
        $resultCode = fwrite($this->resource, $data);
        if ($resultCode === false) {
            throw new ClientException("Unable to write to stream");
        }

        return $resultCode;
    }

    private function read(): string
    {
        $response = '';
        while (false !== $buffer = fgets($this->resource)) {
            if ($buffer === "\r\n") {
                break;
            }
            $response .= $buffer;
        }
        return $response;
    }
}
