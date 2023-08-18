<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Fragment;

class FragmentSequenceFactory
{
    public function getSequence(): \Generator
    {
        $fin = new FinFragment();
        $opcode = new OpcodeFragment();
        $masked = new MaskedFragment();
        $payloadLength = new PayloadLengthFragment();
        $payloadLength16bit = new PayloadLength16BitFragment($payloadLength);
        $payloadLength64bit = new PayloadLength64BitFragment($payloadLength);
        $maskingKey = new MaskingKeyFragment($masked);
        $payload = new PayloadFragment($maskingKey);
        $payload->setPayloadLengthEntities($payloadLength64bit, $payloadLength16bit, $payloadLength);

        $sequence = [
            $fin,
            $opcode,
            $masked,
            $payloadLength,
            $payloadLength16bit,
            $payloadLength64bit,
            $maskingKey,
            $payload
        ];

        foreach ($sequence as $fragment) {
            yield $fragment;
        }
    }
}
