<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;
use FragmentBypassableAwareTrait;

class MaskingKeyFragmentHandler implements FragmentUnpackableAwareInterface, FragmentLengthAwareInterface, FragmentBypassableAwareInterface
{
    use FragmentBypassableAwareTrait;

    private const LENGTH = 4;
    private const BITMASK = 0x80;
    private ?int $value;
    public function unpack(string $binary): void
    {
        [$byte] = array_values(unpack('C', $binary));

        $this->value = $byte & self::BITMASK;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLength(): int
    {
        return self::LENGTH;
    }

    protected function getBypassCallbackArgs()
    {
        return;
    }
}
