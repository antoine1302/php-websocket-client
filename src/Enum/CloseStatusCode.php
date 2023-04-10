<?php

namespace Totoro1302\PhpWebsocketClient\Enum;

enum CloseStatusCode: int
{
    case Normal = 1000;
    case GoingAway = 1001;
    case ProtocolError = 1002;
    case UnhandledType = 1003;
    case NotConsistentData = 1007;
    case PolicyViolation = 1008;
    case OverflowData = 1009;
    case UnhandledExtension = 1010;
    case UnexpectedServerError = 1011;
}
