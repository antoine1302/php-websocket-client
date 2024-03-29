<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Frame;

use Totoro1302\PhpWebsocketClient\Exception\{StreamSocketException, WebSocketProtocolException};
use Totoro1302\PhpWebsocketClient\Service\Fragment\{FragmentAwareInterface,
    FragmentBypassableAwareInterface,
    FragmentPullableAwareInterface,
    FragmentSequenceFactory,
    FragmentStorableAwareInterface
};
use Totoro1302\PhpWebsocketClient\VO\Frame;

readonly class Reader
{
    public function __construct(private FragmentSequenceFactory $sequenceFactory)
    {
    }

    public function read($resource): Frame
    {
        if (!is_resource($resource)) {
            throw new StreamSocketException('Try to use a non-resource type');
        }

        $binaryData = '';
        $fragmentList = [];

        foreach ($this->sequenceFactory->getSequence() as $fragment) {
            if (!$fragment instanceof FragmentAwareInterface) {
                throw new WebSocketProtocolException("Fragment must implement FragmentAwareInterface");
            }

            if ($fragment instanceof FragmentBypassableAwareInterface) {
                if ($fragment->isBypassable()) {
                    continue;
                }
            }

            if ($fragment instanceof FragmentPullableAwareInterface) {
                $binaryData = $this->receive($fragment, $resource);
            }

            $fragment->load($binaryData);

            if ($fragment instanceof FragmentStorableAwareInterface) {
                $fragmentList[$fragment->getKey()] = $fragment->getValue();
            }
        }

        return new Frame(...$fragmentList);
    }

    private function receive(FragmentPullableAwareInterface $fragment, $resource): string
    {
        if ($fragment->getPullLength() === 0) {
            return '';
        }

        $data = fread($resource, $fragment->getPullLength());

        if ($data === false) {
            throw new StreamSocketException('Cannot read from stream socket resource');
        }

        return $data;
    }
}
