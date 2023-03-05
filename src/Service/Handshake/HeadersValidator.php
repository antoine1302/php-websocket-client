<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Handshake;

use Totoro1302\PhpWebsocketClient\Exception\WebSocketProtocolException;

class HeadersValidator
{
    private const HANDSHAKE_MAGIC_STRING = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    private const HTTP_RESPONSE_LINE_STATUS = 'HTTP/1.1 101 Switching Protocols';
    private const HTTP_UPGRADE_HEADER = 'Upgrade: websocket';
    private const HTTP_CONNECTION_HEADER = 'Connection: Upgrade';
    private const WEBSOCKET_ACCEPT_HEADER = '/^Sec-WebSocket-Accept:\s(.+)$/';

    public function validate(string $websocketKey, string $responseHeaders): true
    {
        $headers = explode(PHP_EOL, $responseHeaders);

        $this->assertHttpStatusIsValid($headers);
        $this->assertUpgradeHeaderIsValid($headers);
        $this->assertConnectionHeaderIsValid($headers);

        $expectedKey = $this->getExpectedKey($websocketKey);
        $this->assertWebsocketAcceptHeaderIsValid($headers, $expectedKey);

        return true;
    }

    private function assertHttpStatusIsValid(array $responseHeaders): void
    {
        foreach ($responseHeaders as $header) {
            if (trim($header) === self::HTTP_RESPONSE_LINE_STATUS) {
                return;
            }
        }

        throw new WebSocketProtocolException("HTTP Status-Line not found");
    }

    private function assertUpgradeHeaderIsValid(array $responseHeaders): void
    {
        foreach ($responseHeaders as $header) {
            if (trim($header) === self::HTTP_UPGRADE_HEADER) {
                return;
            }
        }

        throw new WebSocketProtocolException("Upgrade header not found");
    }

    private function assertConnectionHeaderIsValid(array $responseHeaders): void
    {
        foreach ($responseHeaders as $header) {
            if (trim($header) === self::HTTP_CONNECTION_HEADER) {
                return;
            }
        }

        throw new WebSocketProtocolException("Connection header not found");
    }

    private function assertWebsocketAcceptHeaderIsValid(array $responseHeaders, string $expectedKey): void
    {
        foreach ($responseHeaders as $header) {
            if (preg_match(self::WEBSOCKET_ACCEPT_HEADER, $header, $matched) > 0) {
                if (trim($matched[1]) === $expectedKey) {
                    return;
                }
            }
        }

        throw new WebSocketProtocolException("Sec-Websocket-Accept header not found or not valid");
    }

    private function getExpectedKey(string $websocketKey): string
    {
        return base64_encode(sha1($websocketKey . self::HANDSHAKE_MAGIC_STRING, true));
    }
}
