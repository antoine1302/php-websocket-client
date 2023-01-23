<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Hansdshake;

class KeyValidator
{
    private const HANDSHAKE_MAGIC_STRING = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
    public function validate(string $websocketKey, string $websocketKeyAccept): bool
    {
        $keyExpected = base64_encode(sha1($websocketKey . self::HANDSHAKE_MAGIC_STRING, true));

        return $websocketKeyAccept === $keyExpected;
    }
}
