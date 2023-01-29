<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Frame;

use Totoro1302\PhpWebsocketClient\Enum\FragmentSize;
use Totoro1302\PhpWebsocketClient\Enum\Opcode;
use Totoro1302\PhpWebsocketClient\Exception\StreamSocketException;
use Totoro1302\PhpWebsocketClient\VO\Frame;

class Deserializer
{
    private bool $finbit;
    private Opcode $opcode;


    public function deserialize($resource): Frame
    {
        if (!is_resource($resource)) {
            throw new StreamSocketException('Try to deserialize a non-resource type');
        }

        foreach ($this->getDeserializeSequence() as $fragmentSize) {
            $data = $this->read($fragmentSize, $resource);


        }


        return new Frame();
    }

    private function read(FragmentSize $fragmentSize, $resource)
    {

        $response = stream_socket_recvfrom($resource, $fragmentSize->value);

        if ($response === false) {
            throw new StreamSocketException('Cannot read from stream socket resource');
        }

        return $response;
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
}
