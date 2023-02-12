<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

interface FragmentStorableAwareInterface
{
    public function getKey(): string;
}
