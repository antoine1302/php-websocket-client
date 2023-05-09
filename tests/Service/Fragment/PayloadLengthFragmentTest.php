<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Tests\Service\Fragment;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadLengthFragment;

class PayloadLengthFragmentTest extends TestCase
{
    private const FRAGMENT_DATA = 0xEA;

    public function testFragmentLenghtIsValid(): void
    {
        $fragment = new PayloadLengthFragment();
        $fragment->load(pack('C', self::FRAGMENT_DATA));
        $this->assertEquals(0x6A, $fragment->getPayloadLength());
    }
}
