<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

interface FragmentUnpackableAwareInterface
{
    public function unpack(string $binaryData): void;
}
