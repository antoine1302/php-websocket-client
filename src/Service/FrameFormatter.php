<?php

declare(strict_types=1);

namespace Totoro1302\Service;
use Totoro1302\PhpWebsocketClient\VO\Frame;

class FrameFormatter
{
    public function format($resource): Frame
    {
        return new Frame();
    }
}
