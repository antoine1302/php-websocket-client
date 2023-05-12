<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Tests\Service\Fragment;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Service\Fragment\FragmentPayloadLengthAwareInterface;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadLength16BitFragment;

class PayloadLength16BitFragmentTest extends TestCase
{
    private const PAYLOAD_LENGTH_16BIT = 0xD35C;

    public function testIGetValidPayloadLength(): void
    {
        $fragmentPayloadMock = $this->createMock(FragmentPayloadLengthAwareInterface::class);
        $fragment = new PayloadLength16BitFragment($fragmentPayloadMock);
        $fragment->load(pack('n', self::PAYLOAD_LENGTH_16BIT));
        $this->assertEquals(self::PAYLOAD_LENGTH_16BIT, $fragment->getPayloadLength());
    }

    public function testFragmentIsNotBypassable(): void
    {
        $fragmentPayloadMock = $this->createMock(FragmentPayloadLengthAwareInterface::class);
        $fragmentPayloadMock->method('getPayloadLength')->willReturn(0x7E);
        $fragment = new PayloadLength16BitFragment($fragmentPayloadMock);

        $this->assertFalse($fragment->isBypassable());
    }

    public function testFragmentIsBypassable(): void
    {
        $fragmentPayloadMock = $this->createMock(FragmentPayloadLengthAwareInterface::class);
        $fragmentPayloadMock->method('getPayloadLength')->willReturn(0x7D);
        $fragment = new PayloadLength16BitFragment($fragmentPayloadMock);

        $this->assertTrue($fragment->isBypassable());
    }
}
