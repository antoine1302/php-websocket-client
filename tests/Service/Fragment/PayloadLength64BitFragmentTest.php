<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Tests\Service\Fragment;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Service\Fragment\FragmentPayloadLengthAwareInterface;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadLength64BitFragment;

class PayloadLength64BitFragmentTest extends TestCase
{
    private const PAYLOAD_LENGTH_64BIT = 0xC084000807E;

    public function testIGetValidPayloadLength(): void
    {
        $fragmentPayloadMock = $this->createMock(FragmentPayloadLengthAwareInterface::class);
        $fragment = new PayloadLength64BitFragment($fragmentPayloadMock);
        $fragment->load(pack('J', self::PAYLOAD_LENGTH_64BIT));
        $this->assertEquals(self::PAYLOAD_LENGTH_64BIT, $fragment->getPayloadLength());
    }

    public function testFragmentIsNotBypassable(): void
    {
        $fragmentPayloadMock = $this->createMock(FragmentPayloadLengthAwareInterface::class);
        $fragmentPayloadMock->method('getPayloadLength')->willReturn(0x7F);
        $fragment = new PayloadLength64BitFragment($fragmentPayloadMock);

        $this->assertFalse($fragment->isBypassable());
    }

    public function testFragmentIsBypassable(): void
    {
        $fragmentPayloadMock = $this->createMock(FragmentPayloadLengthAwareInterface::class);
        $fragmentPayloadMock->method('getPayloadLength')->willReturn(0x7D);
        $fragment = new PayloadLength64BitFragment($fragmentPayloadMock);

        $this->assertTrue($fragment->isBypassable());
    }
}
