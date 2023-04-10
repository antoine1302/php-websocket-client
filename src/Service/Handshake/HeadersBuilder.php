<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Handshake;

use Psr\Http\Message\UriInterface;

readonly class HeadersBuilder
{
    private const WEBSOCKET_VERSION = 13;

    public function build(UriInterface $uri, string $handshakeKey): string
    {
        $fullPath = $uri->getPath() ?: '/';
        $fullPath .= $uri->getQuery();

        return sprintf(self::getHeaders(), $fullPath, $uri->getAuthority(), self::WEBSOCKET_VERSION, $handshakeKey);
    }

    private static function getHeaders(): string
    {
        return <<<END
            GET %s HTTP/1.1\r
            Host: %s\r
            Connection: Upgrade\r
            Pragma: no-cache\r
            Cache-Control: no-cache\r
            Upgrade: websocket\r
            Sec-WebSocket-Version: %s\r
            Sec-WebSocket-Key: %s\r
            \r\n
            END;
    }
}
