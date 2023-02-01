<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

class PayloadExtendedFragmentHandler
{
    private int $value = 0;
    public function unpack(string $binary): void
    {
        [$byte] = array_values(unpack('n', $binary));

        $this->value = $byte;
    }

    public function getValue()
    {
        return $this->value;
    }
}
