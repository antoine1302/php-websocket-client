<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Enum;

enum FragmentSize: int
{
    case FinAndOpcode = 1;
    case MaskAndPayload = 1;
    case PayloadExtended16bit = 2;
    case PayloadExtended64bit = 8;
    case MaskingKey = 4;
}
