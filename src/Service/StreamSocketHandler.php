<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Totoro1302\PhpWebsocketClient\Exception\StreamSocketConnectionException;
use Totoro1302\PhpWebsocketClient\Service\Hansdshake\HeadersBuilder;
use Totoro1302\PhpWebsocketClient\Service\Hansdshake\KeyGenerator;
use Totoro1302\PhpWebsocketClient\Service\Hansdshake\KeyValidator;

class StreamSocketHandler
{
    private $resource;
    private bool $isPersistent = false;
    private UriFactoryInterface $uriFactory;
    private KeyGenerator $keyGenerator;
    private KeyValidator $keyValidator;
    private HeadersBuilder $headersBuilder;

    public function __construct(
        UriFactoryInterface $uriFactory,
        KeyGenerator $keyGenerator,
        KeyValidator $keyValidator,
        HeadersBuilder $headersBuilder
    )
    {
        $this->uriFactory = $uriFactory;
        $this->keyGenerator = $keyGenerator;
        $this->keyValidator = $keyValidator;
        $this->headersBuilder = $headersBuilder;
    }

    public function initiate(string $originalUri, int $connectionTimeout, bool $isPersistent): void
    {
        // Instanciate a connection uri from the original uri
        $uri = $this->uriFactory->createUri($originalUri);
        $connectionUri = self::createConnectionUri($uri);

        // Add persistent flag
        $flags = STREAM_CLIENT_CONNECT;
        if ($isPersistent) {
            $this->isPersistent = $isPersistent;
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
            $exception = new StreamSocketConnectionException('Unable to initiate socket connection');
            // Add log error/warning here
            throw $exception;
        }

        // Add log info here

        $handshakeKey = $this->keyGenerator->generate();

        $handshakeHeaders = $this->headersBuilder->build($uri, $handshakeKey, null, null);

        $this->write($handshakeHeaders);

        $response = $this->read();

        // Validate response headers
    }

    public function write(string $data, ?int $length = null): void
    {
        stream_socket_sendto($this->resource, $data);
    }

    public function read(): string
    {
        $response = '';
        while (false !== $buffer = stream_socket_recvfrom($this->resource, 1024)) {
            $response .= $buffer;
        }
        return $response;
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
}
