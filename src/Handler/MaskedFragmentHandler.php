<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

class MaskedFragmentHandler
{
    private const BITMASK = 0x80;
    private ?bool $value;
    public function unpack(string $binary): void
    {
        [$byte] = array_values(unpack('C', $binary));

        $this->value = ($byte & self::BITMASK) === self::BITMASK;
    }

    public function getValue()
    {
        return $this->value;
    }
}
