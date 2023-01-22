<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Totoro1302\PhpWebsocketClient\Exception\StreamSocketConnectionException;
use Totoro1302\PhpWebsocketClient\Service\Hansdshake\HeadersBuilder;
use Totoro1302\PhpWebsocketClient\Service\Hansdshake\KeyGenerator;

class StreamSocketHandler
{
    private $resource;
    private bool $isPersistent = false;
    private UriFactoryInterface $uriFactory;
    private KeyGenerator $keyGenerator;
    private HeadersBuilder $headersBuilder;

    public function __construct(
        UriFactoryInterface $uriFactory,
        KeyGenerator $keyGenerator,
        HeadersBuilder $headersBuilder
    ) {
        $this->uriFactory = $uriFactory;
        $this->keyGenerator = $keyGenerator;
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
            // Add log here
            throw $exception;
        }

        $handshakeKey = $this->keyGenerator->generate();

        $handshakeHeaders = $this->headersBuilder->build($uri, $handshakeKey, null, null);

        // Validate handshake
    }

    public function write()
    {
    }
    public function read()
    {
        $response = '';
        while(feof($this->resource) === false){
            $buffer = stream_get_line($this->resource, 1024, PHP_EOL);
            if($buffer === false){
                throw new StreamSocketConnectionException('Failed to read stream');
            }
            $response .= $buffer;
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
}
