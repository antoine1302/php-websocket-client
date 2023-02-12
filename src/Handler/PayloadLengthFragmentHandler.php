<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

class PayloadLengthFragmentHandler implements FragmentUnpackableAwareInterface
{
    private const BITMASK = 0x7F;
    private int $value = 0;

    public function unpack(string $binaryData): void
    {
        [$byte] = array_values(unpack('C', $binaryData));

        $this->value = $byte & self::BITMASK;

    }

    public function bypassCallback(int $payloadLength): bool
    {
        return $this->value < $payloadLength;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
