<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

class PayloadFragmentHandler
{
    private const BITMASK = 0x7F;
    private const FRAGMENT_SIZE = 4;
    private int $value = 0;
    
    public function unpack(string $binaryData): void
    {
        [$byte] = array_values(unpack('N', $binaryData));

    }

    public function getFragmentSize(): int
    {
        return self::FRAGMENT_SIZE;
    }

    public function getValue()
    {
        return $this->value;
    }
}
