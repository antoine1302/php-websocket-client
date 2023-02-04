<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

interface FragmentBypassableAwareInterface
{
    public function isBypassable(): bool;
    public function setBypassCallback(\Closure $closure): void;
}
