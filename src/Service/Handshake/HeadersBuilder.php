<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Hansdshake;
use Psr\Http\Message\UriInterface;

class HeadersBuilder
{
    private const WEBSOCKET_VERSION = 13;

    public function build(UriInterface $uri, string $handshakeKey, ?array $subProtocols, ?string $origin): string
    {

        $headers = sprintf(self::getHeaders(), $uri->getPath() ?: '/', $uri->getAuthority(), self::WEBSOCKET_VERSION, $handshakeKey);

        if (!empty($subProtocols)) {
            $headers .= PHP_EOL . self::addWebsocketSubProtocolHeader($subProtocols);
        }

        if (!empty($origin)) {
            $headers .= PHP_EOL . self::addOriginHeader($origin);
        }

        return $headers;
    }

    private static function getHeaders(): string
    {
        return <<<END
            GET %s HTTP/1.1
            Host: %s
            Connection: Upgrade
            Pragma: no-cache
            Cache-Control: no-cache
            Upgrade: websocket
            Sec-WebSocket-Version: %s
            Sec-WebSocket-Key: %s
            END;
    }

    private static function addWebsocketSubProtocolHeader(array $subProtocols): string
    {
        $subProtocols = implode($subProtocols, ', ');

        return "Sec-WebSocket-Protocol: $subProtocols";
    }

    private static function addOriginHeader(string $origin): string
    {
        return "Origin: $origin";
    }
}
