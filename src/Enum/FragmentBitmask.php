<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Enum;

enum FragmentBitmask: int
{
    case Fin = 0x80;
    case Opcode = 0x0F;
    case Mask = 0x80;
    case PayloadLength = 0x7F;
}
