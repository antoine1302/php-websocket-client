<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Totoro1302\PhpWebsocketClient\Exception\ClientException;
use Totoro1302\PhpWebsocketClient\Service\Hansdshake\{HeadersBuilder, KeyGenerator, KeyValidator};

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ClientConfigInterface $clientConfig;
    private UriFactoryInterface $uriFactory;
    private KeyGenerator $keyGenerator;
    private KeyValidator $keyValidator;
    private HeadersBuilder $headersBuilder;
    private $resource;

    public function __construct(
        ClientConfigInterface $clientConfig,
        UriFactoryInterface $uriFactoryInterface,
        KeyGenerator $keyGenerator,
        KeyValidator $keyValidator
    ) {
        $this->clientConfig = $clientConfig;
        $this->uriFactory = $uriFactoryInterface;
        $this->keyGenerator = $keyGenerator;
        $this->keyValidator = $keyValidator;
    }

    public function __destruct()
    {
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
            $clientKey,
            $this->clientConfig->getSubProtocols(),
            $this->clientConfig->getOrigin()
        );

        $this->write($clientHeaders);

        $serverHeaders = $this->read();

        if (false === $this->keyValidator->validate($clientKey, $serverHeaders)) {
            throw new ClientException("Unable to validate key from server");
        }
    }

    public function disconnect(): void
    {
    }

    public function isConnected(): bool
    {
        return true;
    }

    public function pull(): void
    {
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
            (float) $connectionTimeout,
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
            ->withUserInfo('')
        ;

        return (string) $connectionUri;
    }

    private function write(string $data): int
    {
        $resultCode = stream_socket_sendto($this->resource, $data);
        if ($resultCode === false) {
            throw new ClientException("Unable to write to stream");
        }

        return $resultCode;
    }

    private function read(int $length = 1024): string
    {
        $response = '';
        while (false !== $buffer = stream_socket_recvfrom($this->resource, $length)) {
            $response .= $buffer;
        }
        return $response;
    }
}
