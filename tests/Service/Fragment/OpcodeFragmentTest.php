<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Tests\Service\Fragment;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Enum\Opcode;
use Totoro1302\PhpWebsocketClient\Exception\WebSocketProtocolException;
use Totoro1302\PhpWebsocketClient\Service\Fragment\OpcodeFragment;

class OpcodeFragmentTest extends TestCase
{
    private const OPCODE_CONTINUATION_DATA = 0x80;
    private const OPCODE_TEXT_DATA = 0x81;
    private const OPCODE_BINARY_DATA = 0x82;
    private const OPCODE_PING_DATA = 0x89;
    private const OPCODE_PONG_DATA = 0x8A;
    private const OPCODE_CLOSE_DATA = 0x88;
    private const OPCODE_INVALID_DATA = 0xAE;
    public function testFragmentOpcodeIsContinuation(): void
    {
        $fragment = new OpcodeFragment();
        $fragment->load(pack('C', self::OPCODE_CONTINUATION_DATA));
        $this->assertInstanceOf(Opcode::class, $fragment->getValue());
        $this->assertEquals(Opcode::Continuation, $fragment->getValue());
    }

    public function testFragmentOpcodeIsText(): void
    {
        $fragment = new OpcodeFragment();
        $fragment->load(pack('C', self::OPCODE_TEXT_DATA));
        $this->assertInstanceOf(Opcode::class, $fragment->getValue());
        $this->assertEquals(Opcode::Text, $fragment->getValue());
    }

    public function testFragmentOpcodeIsBinary(): void
    {
        $fragment = new OpcodeFragment();
        $fragment->load(pack('C', self::OPCODE_BINARY_DATA));
        $this->assertInstanceOf(Opcode::class, $fragment->getValue());
        $this->assertEquals(Opcode::Binary, $fragment->getValue());
    }

    public function testFragmentOpcodeIsPing(): void
    {
        $fragment = new OpcodeFragment();
        $fragment->load(pack('C', self::OPCODE_PING_DATA));
        $this->assertInstanceOf(Opcode::class, $fragment->getValue());
        $this->assertEquals(Opcode::Ping, $fragment->getValue());
    }

    public function testFragmentOpcodeIsPong(): void
    {
        $fragment = new OpcodeFragment();
        $fragment->load(pack('C', self::OPCODE_PONG_DATA));
        $this->assertInstanceOf(Opcode::class, $fragment->getValue());
        $this->assertEquals(Opcode::Pong, $fragment->getValue());
    }

    public function testFragmentOpcodeIsClose(): void
    {
        $fragment = new OpcodeFragment();
        $fragment->load(pack('C', self::OPCODE_CLOSE_DATA));
        $this->assertInstanceOf(Opcode::class, $fragment->getValue());
        $this->assertEquals(Opcode::Close, $fragment->getValue());
    }

    public function testItThrowsExceptionIfNotValidOpcode(): void
    {
        $this->expectException(WebSocketProtocolException::class);
        $this->expectExceptionMessage('Invalid Opcode');
        $fragment = new OpcodeFragment();
        $fragment->load(pack('C', self::OPCODE_INVALID_DATA));
    }

    public function testFragmentOpcodeKeyIsValid(): void
    {
        $fragment = new OpcodeFragment();
        $this->assertEquals('opcode', $fragment->getKey());
    }
}
