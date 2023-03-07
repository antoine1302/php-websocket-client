<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Fragment;

class PayloadLengthFragment implements FragmentAwareInterface, FragmentPayloadLengthAwareInterface
{
    private const BITMASK = 0x7F;
    private ?int $value = null;

    public function load(string $binaryData): void
    {
        [$byte] = array_values(unpack('C', $binaryData));

        $this->value = $byte & self::BITMASK;
    }

    public function getPayloadLength(): ?int
    {
        return $this->value;
    }
}
