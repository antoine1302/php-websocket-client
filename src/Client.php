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
    private $resource;

    private int $currentState = self::STATE_CLOSED;
    private bool $isAlive = true;

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
        $this->shutdownSocket();
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

        if (!$this->headersValidator->validate($clientKey, $serverHeaders)) {
            throw new ClientException("Unable to validate server response headers");
        }

        $this->registerSignalHandler();
        $this->currentState = self::STATE_CONNECTED;
    }

    public function pull(): string
    {
        $payload = '';

        while ($this->isRunning()) {
            $frame = $this->reader->read($this->resource);
            switch ($frame->getOpcode()) {
                case Opcode::Ping:
                    $this->pong($frame->getPayload());
                    break;
                case Opcode::Close:
                    if ($this->currentState === self::STATE_CONNECTED) {
                        $this->close($frame->getPayload());
                    }
                    $this->currentState = self::STATE_CLOSED;
                    $this->shutdownSocket();
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

    public function push(string $payload, Opcode $opcode, bool $isMasked = true): void
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

    public function isRunning(): bool
    {
        return $this->currentState > self::STATE_STARTING;
    }

    public function checkHeartbeat(): void
    {
        $pid = pcntl_fork();

        if ($pid < 0) {
            throw new ClientException("Failed to fork during heartbeat");
        } elseif ($pid > 0) {
            // Do nothing in parent process
        } else {
            while (true) {
                echo 'From child...' . PHP_EOL;
                sleep(5);
            }
        }
    }

    private function shutdownSocket(): void
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }

    private function requestTerminate(): void
    {
        $this->currentState = self::STATE_TERMINATING;
        $this->close(pack('n', CloseStatusCode::Normal->value));
    }

    private function open(UriInterface $uri, int $connectionTimeout): void
    {
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
            $exception = new ClientException("Unable to open stream socket connection");
            // Todo: Add log error/warning here
            throw $exception;
        }
    }

    private function registerSignalHandler(): void
    {
        pcntl_signal(SIGTERM, $this->requestTerminate(...));
        pcntl_signal(SIGINT, $this->requestTerminate(...));
        pcntl_signal(SIGQUIT, $this->requestTerminate(...));
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
        $resultCode = stream_socket_sendto($this->resource, $data);
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
