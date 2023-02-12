<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

class FinFragmentHandler implements FragmentUnpackableAwareInterface, FragmentPullableAwareInterface, FragmentStorableAwareInterface
{
    private const BITMASK = 0x80;
    private const LENGTH = 1;
    private const KEY = 'finBit';
    private bool $value;

    public function unpack(string $binaryData): void
    {
        [$byte] = array_values(unpack('C', $binaryData));

        $this->value = ($byte & self::BITMASK) === self::BITMASK;
    }

    public function getLength(): int
    {
        return self::LENGTH;
    }

    public function getValue(): bool
    {
        return $this->value;
    }

    public function getKey(): string
    {
        return self::KEY;
    }
}
