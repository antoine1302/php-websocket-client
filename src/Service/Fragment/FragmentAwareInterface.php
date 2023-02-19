<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Fragment;

interface FragmentAwareInterface
{
    public function load(string $binaryData): void;
}
