<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Fragment;

class PayloadLength16BitFragment implements FragmentAwareInterface, FragmentPullableAwareInterface, FragmentPayloadLengthAwareInterface, FragmentBypassableAwareInterface
{
    public const PAYLOAD_INDEX = 126;
    public const PAYLOAD_THRESHOLD = 0xFFFF;
    private const BYTE_LENGTH = 2;
    private ?int $value = null;

    public function __construct(private readonly FragmentPayloadLengthAwareInterface $fragment)
    {
    }

    public function load(string $binaryData): void
    {
        [$byte] = array_values(unpack('n', $binaryData));

        $this->value = $byte;
    }

    public function getPullLength(): int
    {
        return self::BYTE_LENGTH;
    }

    public function getPayloadLength(): ?int
    {
        return $this->value;
    }

    public function isBypassable(): bool
    {
        return self::PAYLOAD_INDEX !== $this->fragment->getPayloadLength();
    }
}
