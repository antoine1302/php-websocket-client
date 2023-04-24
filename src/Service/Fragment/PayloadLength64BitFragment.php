<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Fragment;

class PayloadLength64BitFragment implements FragmentAwareInterface, FragmentPullableAwareInterface, FragmentPayloadLengthAwareInterface, FragmentBypassableAwareInterface
{
    public const PAYLOAD_INDEX = 127;
    private const BYTE_LENGTH = 8;
    private ?int $value = null;

    public function __construct(private readonly FragmentPayloadLengthAwareInterface $fragment)
    {
    }

    public function load(string $binaryData): void
    {
        [$byte] = array_values(unpack('J', $binaryData));

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
        return self::PAYLOAD_INDEX !== $this->fragment->getpayloadLength();
    }
}
