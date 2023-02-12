<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

use FragmentBypassableAwareTrait;

class PayloadLengthExtended16bitFragmentHandler implements FragmentUnpackableAwareInterface, FragmentPullableAwareInterface, FragmentBypassableAwareInterface
{
    use FragmentBypassableAwareTrait;

    private const LENGTH = 2;
    private const PAYLOAD_ID = 126;
    private int $value;
    private \Closure $callback;

    public function unpack(string $binary): void
    {
        [$byte] = array_values(unpack('n', $binary));

        $this->value = $byte;
    }

    public function getLength(): int
    {
        return self::LENGTH;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    protected function getBypassCallbackArgs(): int
    {
        return self::PAYLOAD_ID;
    }
}
