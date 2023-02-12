<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Frame;

use Totoro1302\PhpWebsocketClient\Exception\StreamSocketException;
use Totoro1302\PhpWebsocketClient\Exception\WebSocketProtocolException;
use Totoro1302\PhpWebsocketClient\Handler\FragmentBypassableAwareInterface;
use Totoro1302\PhpWebsocketClient\Handler\FragmentPullableAwareInterface;
use Totoro1302\PhpWebsocketClient\Handler\FragmentStorableAwareInterface;
use Totoro1302\PhpWebsocketClient\Handler\FragmentUnpackableAwareInterface;
use Totoro1302\PhpWebsocketClient\Service\FragmentSequenceFactory;
use Totoro1302\PhpWebsocketClient\VO\Frame;

class Unpacker
{
    public function __construct(private readonly FragmentSequenceFactory $sequenceFactory)
    {
    }

    public function unpack($resource): Frame
    {
        if (!is_resource($resource)) {
            throw new StreamSocketException('Try to deserialize a non-resource type');
        }

        $binaryData = null;
        $fragmentList = [];

        foreach ($this->sequenceFactory->getSequence() as $fragment) {

            if (!$fragment instanceof FragmentUnpackableAwareInterface) {
                throw new WebSocketProtocolException("Fragment cannot be unpacked");
            }

            if (
                $fragment instanceof FragmentBypassableAwareInterface
                && $fragment->isBypassable()
            ) {
                continue;
            }

            if ($fragment instanceof FragmentPullableAwareInterface) {
                $binaryData = $this->pull($fragment, $resource);
            }

            $fragment->unpack($binaryData);

            if ($fragment instanceof FragmentStorableAwareInterface) {
                $fragmentList[$fragment->getKey()] = $fragment->getValue();
            }
        }

        return new Frame(...$fragmentList);
    }

    private function pull(FragmentPullableAwareInterface $fragment, $resource): string
    {
        $data = stream_socket_recvfrom($resource, $fragment->getLength());

        if ($data === false) {
            throw new StreamSocketException('Cannot read from stream socket resource');
        }

        return $data;
    }
}
