<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Fragment;

class MaskedFragment implements FragmentAwareInterface, FragmentPullableAwareInterface
{
    private const BITMASK = 0x80;
    private const BYTE_LENGTH = 1;
    private bool $value;

    public function load(string $binaryData): void
    {
        [$byte] = array_values(unpack('C', $binaryData));

        $this->value = ($byte & self::BITMASK) === self::BITMASK;
    }

    public function getPullLength(): int
    {
        return self::BYTE_LENGTH;
    }

    public function isMasked(): bool
    {
        return $this->value;
    }

}
