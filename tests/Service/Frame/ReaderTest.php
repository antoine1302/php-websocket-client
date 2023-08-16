<?php

namespace Totoro1302\PhpWebsocketClient\Tests\Service\Frame;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Exception\StreamSocketException;
use Totoro1302\PhpWebsocketClient\Exception\WebSocketProtocolException;
use Totoro1302\PhpWebsocketClient\Service\Fragment\FragmentSequenceFactory;
use Totoro1302\PhpWebsocketClient\Service\Frame\Reader;

class ReaderTest extends TestCase
{
    public function testItThrowsExceptionIfNotResource(): void
    {
        $reader = new Reader($this->getFragmentSequenceFactoryMock());

        $this->expectException(StreamSocketException::class);
        $this->expectExceptionMessage('Try to use a non-resource type');

        $notResource = 'This is not a resource';
        $reader->read($notResource);
    }
    public function testItThrowsExceptionIfFragmentIsInvalid(): void
    {
        $reader = new Reader($this->getInvalidFragmentSequenceFactoryMock());

        $this->expectException(WebSocketProtocolException::class);
        $this->expectExceptionMessage("Fragment must implement FragmentAwareInterface");

        $resource = fopen('php://memory', 'r');
        $reader->read($resource);
        fclose($resource);
    }

    /**
     * @return FragmentSequenceFactory&MockObject
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function getFragmentSequenceFactoryMock(): FragmentSequenceFactory
    {
        return $this->createMock(FragmentSequenceFactory::class);
    }

    /**
     * @return FragmentSequenceFactory&MockObject
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function getInvalidFragmentSequenceFactoryMock(): FragmentSequenceFactory
    {
        $factory = $this->createMock(FragmentSequenceFactory::class);
        $factory->method('getSequence')->willReturn([new \stdClass()]);

        return $factory;
    }
}
