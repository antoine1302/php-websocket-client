<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service;

use Totoro1302\PhpWebsocketClient\Handler\{
    FinFragmentHandler,
    MaskedFragmentHandler,
    MaskingKeyFragmentHandler,
    OpcodeFragmentHandler,
    PayloadLengthExtended16bitFragmentHandler,
    PayloadLengthExtended64bitFragmentHandler,
    PayloadLengthFragmentHandler,
    PayloadFragmentHandler
};

class FragmentSequenceFactory
{
    public function getSequence(): array
    {
        $fin = new FinFragmentHandler();
        $opcode = new OpcodeFragmentHandler();
        $masked = new MaskedFragmentHandler();
        $payloadLength = new PayloadLengthFragmentHandler();
        $payloadLength16bit = new PayloadLengthExtended16bitFragmentHandler();
        $payloadLength64bit = new PayloadLengthExtended64bitFragmentHandler();
        $maskingKey = new MaskingKeyFragmentHandler();
        $payload = new PayloadFragmentHandler();

        $payloadLength16bit->setBypassCallback($payloadLength->bypassCallback(...));
        $payloadLength64bit->setBypassCallback($payloadLength->bypassCallback(...));

        $maskingKey->setBypassCallback($masked->bypassCallback(...));

        $payload->setPayloadLengthFragments($payloadLength64bit, $payloadLength16bit, $payloadLength);

        return [
            $fin,
            $opcode,
            $masked,
            $payloadLength,
            $payloadLength16bit,
            $payloadLength64bit,
            $maskingKey
        ];
    }
}
