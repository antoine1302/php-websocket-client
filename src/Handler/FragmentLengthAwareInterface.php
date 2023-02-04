<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

interface FragmentLengthAwareInterface
{
    public function getLength(): int;
}
