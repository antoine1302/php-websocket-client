<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Fragment;

class PayloadLength16BitFragment implements FragmentAwareInterface, FragmentPullableAwareInterface, FragmentPayloadLengthAwareInterface, FragmentBypassableAwareInterface
{
    private const BYTE_LENGTH = 2;
    private const PAYLOAD_THRESHOLD = 126;
    private ?int $value = null;
    private FragmentPayloadLengthAwareInterface $fragment;

    public function __construct(FragmentPayloadLengthAwareInterface $fragment)
    {
        $this->fragment = $fragment;
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
        return !(self::PAYLOAD_THRESHOLD === $this->fragment->getPayloadLength());
    }
}
