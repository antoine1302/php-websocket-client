<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Tests\Service\Fragment;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Service\Fragment\FinFragment;

class FinFragmentTest extends TestCase
{
    private const BINARY_DATA_IS_FINAL = 0x81;
    private const BINARY_DATA_IS_NOT_FINAL = 0x01;

    public function testFragmentIsFinal(): void
    {
        $finFragment = new FinFragment();
        $finFragment->load(pack('C', self::BINARY_DATA_IS_FINAL));
        $this->assertTrue($finFragment->getValue());
    }

    public function testFragmentIsNotFinal(): void
    {
        $finFragment = new FinFragment();
        $finFragment->load(pack('C', self::BINARY_DATA_IS_NOT_FINAL));
        $this->assertFalse($finFragment->getValue());
    }

    public function testFragmentKeyIsValid(): void
    {
        $finFragment = new FinFragment();
        $this->assertEquals('finBit', $finFragment->getKey());
    }

    public function testFragmentPullLengthIsValid(): void
    {
        $finFragment = new FinFragment();
        $this->assertEquals(1, $finFragment->getPullLength());
    }
}
