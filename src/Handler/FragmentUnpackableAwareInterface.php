<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;
use Totoro1302\PhpWebsocketClient\Enum\Opcode;

interface FragmentUnpackableAwareInterface
{
    public function unpack(string $binaryData): void;

    public function getValue(): mixed;
}
