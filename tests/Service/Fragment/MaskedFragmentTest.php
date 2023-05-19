<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Tests\Service\Fragment;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Service\Fragment\MaskedFragment;

class MaskedFragmentTest extends TestCase
{
    private const FRAGMENT_MASKED_DATA = 0x81;
    private const FRAGMENT_NOT_MASKED_DATA = 0x4C;

    public function testFragmentIsMasked(): void
    {
        $fragment = new MaskedFragment();
        $fragment->load(pack('C', self::FRAGMENT_MASKED_DATA));
        $this->assertTrue($fragment->isMasked());
    }

    public function testFragmentIsNotMasked(): void
    {
        $fragment = new MaskedFragment();
        $fragment->load(pack('C', self::FRAGMENT_NOT_MASKED_DATA));
        $this->assertFalse($fragment->isMasked());
    }

    public function testFragmentPullLengthIsValid(): void
    {
        $fragment = new MaskedFragment();
        $this->assertEquals(1, $fragment->getPullLength());
    }
}
