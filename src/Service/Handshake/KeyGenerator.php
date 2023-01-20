<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Hansdshake;

class KeyGenerator
{
    private const BYTES_LENGTH = 16;
    public function generate(): string
    {
        return base64_encode(random_bytes(self::BYTES_LENGTH));
    }
}
