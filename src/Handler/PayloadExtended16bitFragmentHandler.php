<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;
use FragmentBypassableAwareTrait;

class PayloadExtended16bitFragmentHandler implements FragmentUnpackableAwareInterface, FragmentLengthAwareInterface, FragmentBypassableAwareInterface
{
    use FragmentBypassableAwareTrait;
    private const LENGTH = 2;
    private int $value = 0;
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

    public function getValue()
    {
        return $this->value;
    }

    protected function getBypassCallbackArgs()
    {
        return $this->value;
    }
}
