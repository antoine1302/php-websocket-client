<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Hansdshake;

use Totoro1302\PhpWebsocketClient\Exception\StreamSocketConnectionException;

class KeyValidator
{
    private const HANDSHAKE_MAGIC_STRING = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    private const WEBSOCKET_ACCEPT_HEADER = 'Sec-WebSocket-Accept';

    public function validate(string $websocketKey, string $responseHeaders): bool
    {
        $keyExpected = $this->getExpectedKey($websocketKey);
        $keyAcceptHeader = $this->getKeyAcceptFromHeaders($responseHeaders);

        return $keyExpected === $keyAcceptHeader;
    }

    private function getExpectedKey(string $websocketKey): string
    {
        return base64_encode(sha1($websocketKey . self::HANDSHAKE_MAGIC_STRING, true));
    }

    private function getKeyAcceptFromHeaders(string $responseHeaders): string
    {
        $header = strtok($responseHeaders, PHP_EOL);
        while (false !== $header) {
            if (preg_match('/^Sec-WebSocket-Accept:\s(.+)$/', $header, $matched) > 0) {
                return trim($matched[1]);
            }
            $header = strtok(PHP_EOL);
        }

        throw new StreamSocketConnectionException("Cannot find header: Sec-WebSocket-Accept");
    }
}
