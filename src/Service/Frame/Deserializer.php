<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Frame;

use Totoro1302\PhpWebsocketClient\Enum\FragmentSize;
use Totoro1302\PhpWebsocketClient\Enum\Opcode;
use Totoro1302\PhpWebsocketClient\Enum\PayloadLength;
use Totoro1302\PhpWebsocketClient\Exception\StreamSocketException;
use Totoro1302\PhpWebsocketClient\VO\Frame;

class Deserializer
{
    private bool $finbit;
    private Opcode $opcode;
    private bool $masked;
    private int $payloadLength = 0;

    public function deserialize($resource): Frame
    {
        if (!is_resource($resource)) {
            throw new StreamSocketException('Try to deserialize a non-resource type');
        }

        foreach ($this->getDeserializeSequence() as $fragmentSize) {

            if (
                $fragmentSize === FragmentSize::PayloadExtended16bit
                && $this->payloadLength < PayloadLength::Extended16bit->value
            ) {
                continue;
            }

            if (
                $fragmentSize === FragmentSize::PayloadExtended64bit
                && $this->payloadLength < PayloadLength::Extended64bit->value
            ) {
                continue;
            }

            if ($fragmentSize === FragmentSize::MaskingKey && $this->masked === false) {
                continue;
            }


            // $this->pull($fragmentSize, $resource);
        }


        // return new Frame();
    }

    private function pull(FragmentSize $fragmentSize, $resource): string
    {
        $response = stream_socket_recvfrom($resource, $fragmentSize->value);

        if ($response === false) {
            throw new StreamSocketException('Cannot read from stream socket resource');
        }
    }

    private function getDeserializeSequence(): array
    {
        return [
            FragmentSize::FinAndOpcode,
            FragmentSize::MaskAndPayload,
            FragmentSize::PayloadExtended16bit,
            FragmentSize::PayloadExtended64bit,
            FragmentSize::MaskingKey
        ];
    }

    private function getFinAndOpcode(string $data)
    {
    }

    private function getMaskAndPayload(string $data)
    {
    }

    private function getPayloadExtended16bit(string $data)
    {
    }

    private function getPayloadExtended64bit(string $data)
    {
    }

    private function getMaskingKey(string $data)
    {
    }
}
