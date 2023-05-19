<?php

namespace Totoro1302\PhpWebsocketClient\Tests\Service\Fragment;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Service\Fragment\MaskedFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\MaskingKeyFragment;

class MaskingKeyFragmentTest extends TestCase
{
    public function testIGetCorrectMaskingKey(): void
    {
        $maskedFragmentMock = $this->createMock(MaskedFragment::class);
        $fragment = new MaskingKeyFragment($maskedFragmentMock);
        $maskingKey = random_bytes(4);
        $fragment->load($maskingKey);
        $this->assertEquals($maskingKey, $fragment->getMaskingKey());
    }

    public function testIsBypassableIfNotMasked(): void
    {
        $maskedFragmentMock = $this->createMock(MaskedFragment::class);
        $maskedFragmentMock->method('isMasked')->willReturn(false);
        $fragment = new MaskingKeyFragment($maskedFragmentMock);
        $this->assertTrue($fragment->isBypassable());
    }

    public function testIsNotBypassableIfMasked(): void
    {
        $maskedFragmentMock = $this->createMock(MaskedFragment::class);
        $maskedFragmentMock->method('isMasked')->willReturn(true);
        $fragment = new MaskingKeyFragment($maskedFragmentMock);
        $this->assertFalse($fragment->isBypassable());
    }

    public function testIGetValidPullLength(): void
    {
        $maskedFragmentMock = $this->createMock(MaskedFragment::class);
        $fragment = new MaskingKeyFragment($maskedFragmentMock);

        $this->assertEquals(4, $fragment->getPullLength());
    }
}
