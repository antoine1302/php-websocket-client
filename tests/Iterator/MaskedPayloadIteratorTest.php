<?php

namespace Totoro1302\PhpWebsocketClient\Tests\Iterator;

use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Iterator\MaskedPayloadIterator;

class MaskedPayloadIteratorTest extends TestCase
{
    private const MASKING_KEY = [13, 197, 77, 52];
    private const PAYLOAD_UNMASKED = 'Hello world!';
    private const PAYLOAD_MASKED = [69, 160, 33, 88, 98, 229, 58, 91, 127, 169, 41, 21];

    public function testPayloadIsMaskedIfMaskingKeyProvided(): void
    {
        $maskingKey = pack('C4', ...self::MASKING_KEY);
        $payloadMasked = '';
        $currentIndex = 0;

        foreach (new MaskedPayloadIterator(self::PAYLOAD_UNMASKED, $maskingKey) as $index => $maskedByte) {
            $this->assertEquals($currentIndex, $index);
            $payloadMasked .= $maskedByte;
            $currentIndex++;
        }
        $payloadMaskedUnpack = array_values(unpack('C*', $payloadMasked));

        $this->assertEquals(self::PAYLOAD_MASKED, $payloadMaskedUnpack);
    }
}
