<?php

namespace Totoro1302\PhpWebsocketClient\Tests\VO;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Enum\Opcode;
use Totoro1302\PhpWebsocketClient\VO\Frame;

class FrameTest extends TestCase
{
    public function testVO(): void
    {
        $frame = new Frame(true, Opcode::Text, "Fake payload");

        $this->assertTrue($frame->isFinal());
        $this->assertInstanceOf(Opcode::class, $frame->getOpcode());
        $this->assertEquals("Fake payload", $frame->getPayload());
    }
}
