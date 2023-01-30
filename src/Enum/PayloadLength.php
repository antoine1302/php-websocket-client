<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Enum;

enum PayloadLength: int
{
    case Extended16bit = 126;
    case Extended64bit = 127;
}
