<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Fragment;

interface FragmentStorableAwareInterface
{
    public function getKey(): string;

    public function getValue(): mixed;
}
