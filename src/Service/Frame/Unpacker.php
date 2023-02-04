<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Frame;

use Totoro1302\PhpWebsocketClient\Enum\FragmentSize;
use Totoro1302\PhpWebsocketClient\Enum\Opcode;
use Totoro1302\PhpWebsocketClient\Enum\PayloadLength;
use Totoro1302\PhpWebsocketClient\Exception\StreamSocketException;
use Totoro1302\PhpWebsocketClient\VO\Frame;

class Unpacker
{
    private bool $finbit;
    private Opcode $opcode;
    private bool $masked;
    private int $payloadLength = 0;

    public function unpack($resource): Frame
    {
        if (!is_resource($resource)) {
            throw new StreamSocketException('Try to deserialize a non-resource type');
        }


        // return new Frame();
    }

    private function pull(FragmentSize $fragmentSize, $resource)
    {
        $response = stream_socket_recvfrom($resource, $fragmentSize->value);

        if ($response === false) {
            throw new StreamSocketException('Cannot read from stream socket resource');
        }
    }
}
