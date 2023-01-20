<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class StreamSocketHandler
{
    private $resource;
    private bool $isPersistent = false;
    private UriFactoryInterface $uriFactory;

    public function __construct(UriFactoryInterface $uriFactory)
    {
        $this->uriFactory = $uriFactory;
    }

    public function initiate(string $originalUri, int $connectionTimeout, bool $isPersistent): bool
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
            // Add log
            return false;
        }

        // Continue with the handshake

        return true;
    }

    private static function createConnectionUri(UriInterface $uri): string
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
