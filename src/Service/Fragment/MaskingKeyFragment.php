<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Fragment;

class MaskingKeyFragment implements FragmentAwareInterface, FragmentPullableAwareInterface, FragmentBypassableAwareInterface
{
    private const BYTE_LENGTH = 4;
    private ?string $value = null;

    public function __construct(private readonly MaskedFragment $fragment)
    {
    }

    public function load(string $binaryData): void
    {
        $this->value = $binaryData;
    }

    public function getPullLength(): int
    {
        return self::BYTE_LENGTH;
    }

    public function isBypassable(): bool
    {
        return !$this->fragment->isMasked();
    }

    public function getMaskingKey(): ?string
    {
        return $this->value;
    }
}
