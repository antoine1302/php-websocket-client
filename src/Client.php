<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Totoro1302\PhpWebsocketClient\Exception\ClientException;
use Totoro1302\PhpWebsocketClient\Service\Frame\Reader;
use Totoro1302\PhpWebsocketClient\Service\Handshake\{HeadersBuilder, HeadersValidator, KeyGenerator};

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $resource;

    public function __construct(
        private readonly ClientConfigInterface $clientConfig,
        private readonly UriFactoryInterface $uriFactory,
        private readonly KeyGenerator $keyGenerator,
        private readonly HeadersValidator $headersValidator,
        private readonly HeadersBuilder $headersBuilder,
        private readonly Reader $reader
    ) {
    }

    public function __destruct()
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }

    public function connect(): void
    {
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
    }

    public function disconnect(): void
    {
    }

    public function isConnected(): bool
    {
        return true;
    }

    public function pull(): string
    {
        $frame = $this->reader->read($this->resource);
        return $frame->getPayload();
    }

    public function push(): void
    {
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
