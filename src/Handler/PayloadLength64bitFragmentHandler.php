<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

use FragmentBypassableAwareTrait;

class PayloadLength64bitFragmentHandler implements FragmentUnpackableAwareInterface, FragmentPullableAwareInterface, FragmentBypassableAwareInterface
{
    use FragmentBypassableAwareTrait;

    private const LENGTH = 8;
    private const PAYLOAD_ID = 127;
    private ?int $value;

    public function unpack(string $binary): void
    {
        [$byte] = array_values(unpack('n', $binary));

        $this->value = $byte;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLength(): int
    {
        return self::LENGTH;
    }

    protected function getBypassCallbackArgs(): int
    {
        return self::PAYLOAD_ID;
    }
}
