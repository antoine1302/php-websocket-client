<?php

namespace Totoro1302\PhpWebsocketClient\Tests\Service\Fragment;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Exception\WebSocketProtocolException;
use Totoro1302\PhpWebsocketClient\Service\Fragment\MaskingKeyFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadLength16BitFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadLength64BitFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadLengthFragment;

class PayloadFragmentTest extends TestCase
{
    private const PAYLOAD_LENGTH_64BIT = 0x7090808000;
    private const PAYLOAD_LENGTH_16BIT = 0x9D00;
    private const PAYLOAD_LENGTH = 0x5F;

    public function testIGetValidPullLength64Bit(): void
    {
        $maskingKeyFragmentMock = $this->createMock(MaskingKeyFragment::class);
        $maskingKey = random_bytes(4);
        $maskingKeyFragmentMock->method('getMaskingKey')->willReturn($maskingKey);
        $payloadFragment = new PayloadFragment($maskingKeyFragmentMock);

        $payloadLengthFragmentMock = $this->createMock(PayloadLengthFragment::class);
        $payloadLengthFragmentMock->method('getPayloadLength')->willReturn(null);
        $payloadLength16BitFragmentMock = $this->createMock(PayloadLength16BitFragment::class);
        $payloadLength16BitFragmentMock->method('getPayloadLength')->willReturn(null);
        $payloadLength64BitFragmentMock = $this->createMock(PayloadLength64BitFragment::class);
        $payloadLength64BitFragmentMock->method('getPayloadLength')->willReturn(self::PAYLOAD_LENGTH_64BIT);
        $payloadFragment->setPayloadLengthEntities($payloadLength64BitFragmentMock, $payloadLength16BitFragmentMock, $payloadLengthFragmentMock);

        $this->assertEquals(self::PAYLOAD_LENGTH_64BIT, $payloadFragment->getPullLength());
    }

    public function testIGetValidPullLength16Bit(): void
    {
        $maskingKeyFragmentMock = $this->createMock(MaskingKeyFragment::class);
        $maskingKey = random_bytes(4);
        $maskingKeyFragmentMock->method('getMaskingKey')->willReturn($maskingKey);
        $payloadFragment = new PayloadFragment($maskingKeyFragmentMock);

        $payloadLengthFragmentMock = $this->createMock(PayloadLengthFragment::class);
        $payloadLengthFragmentMock->method('getPayloadLength')->willReturn(null);
        $payloadLength16BitFragmentMock = $this->createMock(PayloadLength16BitFragment::class);
        $payloadLength16BitFragmentMock->method('getPayloadLength')->willReturn(self::PAYLOAD_LENGTH_16BIT);
        $payloadLength64BitFragmentMock = $this->createMock(PayloadLength64BitFragment::class);
        $payloadLength64BitFragmentMock->method('getPayloadLength')->willReturn(null);
        $payloadFragment->setPayloadLengthEntities($payloadLength64BitFragmentMock, $payloadLength16BitFragmentMock, $payloadLengthFragmentMock);

        $this->assertEquals(self::PAYLOAD_LENGTH_16BIT, $payloadFragment->getPullLength());
    }

    public function testIGetValidPullLength(): void
    {
        $maskingKeyFragmentMock = $this->createMock(MaskingKeyFragment::class);
        $maskingKey = random_bytes(4);
        $maskingKeyFragmentMock->method('getMaskingKey')->willReturn($maskingKey);
        $payloadFragment = new PayloadFragment($maskingKeyFragmentMock);

        $payloadLengthFragmentMock = $this->createMock(PayloadLengthFragment::class);
        $payloadLengthFragmentMock->method('getPayloadLength')->willReturn(self::PAYLOAD_LENGTH);
        $payloadLength16BitFragmentMock = $this->createMock(PayloadLength16BitFragment::class);
        $payloadLength16BitFragmentMock->method('getPayloadLength')->willReturn(null);
        $payloadLength64BitFragmentMock = $this->createMock(PayloadLength64BitFragment::class);
        $payloadLength64BitFragmentMock->method('getPayloadLength')->willReturn(null);
        $payloadFragment->setPayloadLengthEntities($payloadLength64BitFragmentMock, $payloadLength16BitFragmentMock, $payloadLengthFragmentMock);

        $this->assertEquals(self::PAYLOAD_LENGTH, $payloadFragment->getPullLength());
    }

    public function testItThrowsExceptionIfNoValidPullLength(): void
    {
        $maskingKeyFragmentMock = $this->createMock(MaskingKeyFragment::class);
        $maskingKey = random_bytes(4);
        $maskingKeyFragmentMock->method('getMaskingKey')->willReturn($maskingKey);
        $payloadFragment = new PayloadFragment($maskingKeyFragmentMock);

        $payloadLengthFragmentMock = $this->createMock(PayloadLengthFragment::class);
        $payloadLengthFragmentMock->method('getPayloadLength')->willReturn(null);
        $payloadLength16BitFragmentMock = $this->createMock(PayloadLength16BitFragment::class);
        $payloadLength16BitFragmentMock->method('getPayloadLength')->willReturn(null);
        $payloadLength64BitFragmentMock = $this->createMock(PayloadLength64BitFragment::class);
        $payloadLength64BitFragmentMock->method('getPayloadLength')->willReturn(null);
        $payloadFragment->setPayloadLengthEntities($payloadLength64BitFragmentMock, $payloadLength16BitFragmentMock, $payloadLengthFragmentMock);

        $this->expectException(WebSocketProtocolException::class);
        $payloadFragment->getPullLength();
    }

    public function testPayloadIsValidAndMasked(): void
    {
        $maskingKeyFragmentMock = $this->createMock(MaskingKeyFragment::class);
        $maskingKey = random_bytes(4);
        $maskingKeyFragmentMock->expects($this->exactly(2))->method('getMaskingKey')->willReturn($maskingKey);
        $fragment = new PayloadFragment($maskingKeyFragmentMock);

        $fragment->load("Hello World!");
        $this->assertNotEquals("Hello World!", $fragment->getValue());
    }

    public function testPayloadIsValidAndNotMasked(): void
    {
        $maskingKeyFragmentMock = $this->createMock(MaskingKeyFragment::class);
        $maskingKeyFragmentMock->expects($this->once())->method('getMaskingKey')->willReturn(null);
        $fragment = new PayloadFragment($maskingKeyFragmentMock);

        $fragment->load("Hello World!");
        $this->assertEquals("Hello World!", $fragment->getValue());
    }

    public function testPayloadFragmentKeyIsValid(): void
    {
        $maskingKeyFragmentMock = $this->createMock(MaskingKeyFragment::class);
        $fragment = new PayloadFragment($maskingKeyFragmentMock);

        $this->assertEquals('payload', $fragment->getKey());
    }
}
