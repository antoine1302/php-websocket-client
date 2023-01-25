<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service;

use Psr\Http\Message\UriInterface;
use Totoro1302\PhpWebsocketClient\Exception\StreamSocketException;

class StreamSocketHandler
{
    private $resource;
    private bool $isPersistent = false;

    public function open(UriInterface $uri, int $connectionTimeout, bool $isPersistent): void
    {
        $connectionUri = $this->createConnectionUri($uri);

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
            $exception = new StreamSocketException("Unable to open stream socket connection");
            // Todo: Add log error/warning here
            throw $exception;
        }
    }

    public function write(string $data): int
    {
        $resultCode = stream_socket_sendto($this->resource, $data);
        if ($resultCode === false) {
            throw new StreamSocketException("Unable to write to stream");
        }

        return $resultCode;
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
