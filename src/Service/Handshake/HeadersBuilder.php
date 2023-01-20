<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Hansdshake;
use Nyholm\Psr7\Factory\Psr17Factory;

class HeadersBuilder
{
    private const WEBSOCKET_VERSION = 13;

    public function build(string $url, string $handshakeKey, ?array $subProtocols = null, ?string $origin = null): string
    {
        $parsedUrl = parse_url($url);

        if ($parsedUrl === false) {
            throw new \RuntimeException("Failed to parse url: $url");
        }

        $this->assertHostIsNotEmpty($parsedUrl);
        $this->assertPortIsValid($parsedUrl);

        $path = $parsedUrl['path'] ?: '/';
        $hostname = $parsedUrl['host'] . ($parsedUrl['port'] ? ':' . $parsedUrl['port'] : '');

        $headers = sprintf(self::getHeaders(), $path, $hostname, self::WEBSOCKET_VERSION, $handshakeKey);

        if (!empty($subProtocols)) {
            $headers .= PHP_EOL . $this->addWebsocketSubProtocolHeader($subProtocols);
        }

        if (!empty($origin)) {
            $headers .= PHP_EOL . $this->addOriginHeader($origin);
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

    private function addWebsocketSubProtocolHeader(array $subProtocols): string
    {
        $subProtocols = implode($subProtocols, ', ');

        return "Sec-WebSocket-Protocol: $subProtocols";
    }

    private function addOriginHeader(string $origin): string
    {
        return "Origin: $origin";
    }

    private function assertHostIsNotEmpty(array $parsedUrl): void
    {
        if (empty($parsedUrl['host'])) {
            throw new \RuntimeException();
        }
    }

    private function assertPortIsValid(array $parsedUrl): void
    {
        if (isset($parsedUrl['port'])) {
            if (!is_int($parsedUrl['port'])) {
                throw new \RuntimeException();

            }
            if ($parsedUrl['port'] <= 0) {
                throw new \RuntimeException();
            }
        }
    }
}
