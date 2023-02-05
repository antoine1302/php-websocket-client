<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service;

use Totoro1302\PhpWebsocketClient\Handler\{
    FinFragmentHandler,
    MaskedFragmentHandler,
    MaskingKeyFragmentHandler,
    OpcodeFragmentHandler,
    PayloadExtended16bitFragmentHandler,
    PayloadExtended64bitFragmentHandler,
    PayloadFragmentHandler
};

class FragmentSequenceFactory
{
    public function getSequence(): array
    {
        $fin = new FinFragmentHandler();
        $opcode = new OpcodeFragmentHandler();
        $masked = new MaskedFragmentHandler();
        $payload = new PayloadFragmentHandler();
        $payloadExtended16bit = new PayloadExtended16bitFragmentHandler();
        $payloadExtended64bit = new PayloadExtended64bitFragmentHandler();
        $maskingKey = new MaskingKeyFragmentHandler();

        $payloadExtended16bit->setBypassCallback($payload->bypassCallback(...));
        $payloadExtended64bit->setBypassCallback($payload->bypassCallback(...));

        $maskingKey->setBypassCallback($masked->bypassCallback(...));

        return [
            $fin,
            $opcode,
            $masked,
            $payload,
            $payloadExtended16bit,
            $payloadExtended64bit,
            $maskingKey
        ];
    }
}
