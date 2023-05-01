<?php

namespace Totoro1302\PhpWebsocketClient\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Enum\CloseStatusCode;

class CloseStatusCodeTest extends TestCase
{
    public function testNormalStatusCode(): void
    {
        $statusCode = CloseStatusCode::Normal;
        $this->assertEquals(1000, $statusCode->value);
    }

    public function testGoingAwayStatusCode(): void
    {
        $statusCode = CloseStatusCode::GoingAway;
        $this->assertEquals(1001, $statusCode->value);
    }

    public function testProtocolErrorStatusCode(): void
    {
        $statusCode = CloseStatusCode::ProtocolError;
        $this->assertEquals(1002, $statusCode->value);
    }

    public function testUnhandledTypeStatusCode(): void
    {
        $statusCode = CloseStatusCode::UnhandledType;
        $this->assertEquals(1003, $statusCode->value);
    }

    public function testNotConsistentDataStatusCode(): void
    {
        $statusCode = CloseStatusCode::NotConsistentData;
        $this->assertEquals(1007, $statusCode->value);
    }

    public function testPolicyViolationStatusCode(): void
    {
        $statusCode = CloseStatusCode::PolicyViolation;
        $this->assertEquals(1008, $statusCode->value);
    }

    public function testOverflowDataStatusCode(): void
    {
        $statusCode = CloseStatusCode::OverflowData;
        $this->assertEquals(1009, $statusCode->value);
    }

    public function testUnhandledExtensionStatusCode(): void
    {
        $statusCode = CloseStatusCode::UnhandledExtension;
        $this->assertEquals(1010, $statusCode->value);
    }

    public function testUnexpectedServerErrorStatusCode(): void
    {
        $statusCode = CloseStatusCode::UnexpectedServerError;
        $this->assertEquals(1011, $statusCode->value);
    }
}
