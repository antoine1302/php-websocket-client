<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Frame;

use Totoro1302\PhpWebsocketClient\Exception\{
    StreamSocketException,
    WebSocketProtocolException
};
use Totoro1302\PhpWebsocketClient\Iterator\FragmentFilterIterator;
use Totoro1302\PhpWebsocketClient\Service\Fragment\{
    FragmentAwareInterface,
    FragmentPullableAwareInterface,
    FragmentSequenceFactory
};
use Totoro1302\PhpWebsocketClient\VO\Frame;

class Reader
{
    public function __construct(private readonly FragmentSequenceFactory $sequenceFactory)
    {
    }

    public function read($resource): Frame
    {
        if (!is_resource($resource)) {
            throw new StreamSocketException('Try to deserialize a non-resource type');
        }

        $binaryData = '';
        $fragmentList = [];

        foreach (new FragmentFilterIterator($this->sequenceFactory->getSequence()) as $fragment) {

            if (!$fragment instanceof FragmentAwareInterface) {
                throw new WebSocketProtocolException("Fragment must implement FragmentAwareInterface");
            }

            if ($fragment instanceof FragmentPullableAwareInterface) {
                $binaryData = $this->pull($fragment, $resource);
            }

            $fragment->load($binaryData);

            if ($fragment instanceof FragmentStorableAwareInterface) {
                $fragmentList[$fragment->getKey()] = $fragment->getValue();
            }
        }

        return new Frame(...$fragmentList);
    }

    private function pull(FragmentPullableAwareInterface $fragment, $resource): string
    {
        $data = stream_socket_recvfrom($resource, $fragment->getPullLength());

        if ($data === false) {
            throw new StreamSocketException('Cannot read from stream socket resource');
        }

        return $data;
    }
}
