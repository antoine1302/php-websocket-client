<?php

namespace Totoro1302\PhpWebsocketClient\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Enum\Opcode;

class OpcodeTest extends TestCase
{
    public function testContinuation(): void
    {
        $opcode = Opcode::Continuation;
        $this->assertEquals(0x00, $opcode->value);
    }

    public function testText(): void
    {
        $opcode = Opcode::Text;
        $this->assertEquals(0x01, $opcode->value);
    }

    public function testBinary(): void
    {
        $opcode = Opcode::Binary;
        $this->assertEquals(0x02, $opcode->value);
    }

    public function testClose(): void
    {
        $opcode = Opcode::Close;
        $this->assertEquals(0x08, $opcode->value);
    }

    public function testPing(): void
    {
        $opcode = Opcode::Ping;
        $this->assertEquals(0x09, $opcode->value);
    }

    public function testPong(): void
    {
        $opcode = Opcode::Pong;
        $this->assertEquals(0x0A, $opcode->value);
    }
}
