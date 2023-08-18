<?php

namespace Totoro1302\PhpWebsocketClient\Tests\Service\Frame;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Totoro1302\PhpWebsocketClient\Enum\Opcode;
use Totoro1302\PhpWebsocketClient\Exception\StreamSocketException;
use Totoro1302\PhpWebsocketClient\Exception\WebSocketProtocolException;
use Totoro1302\PhpWebsocketClient\Service\Fragment\FinFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\FragmentSequenceFactory;
use Totoro1302\PhpWebsocketClient\Service\Fragment\MaskedFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\MaskingKeyFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\OpcodeFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadLength16BitFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadLength64BitFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadLengthFragment;
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

    public function testItThrowsExceptionIfResourceIsInvalid(): void
    {
        $reader = new Reader($this->getFragmentSequenceFactoryMockWithFragmentCollection());

        $this->expectException(StreamSocketException::class);

        $resource = fopen('php://output', 'w');
        $reader->read($resource);
        fclose($resource);
    }

    public function testItCanReadAFrame(): void
    {
        $reader = new Reader($this->getFragmentSequenceFactoryMockWithFragmentCollection());

        $resource = fopen('php://memory', 'r');
        $reader->read($resource);
        fclose($resource);
    }

    /**
     * @return FragmentSequenceFactory&MockObject
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function getFragmentSequenceFactoryMockWithFragmentCollection(): FragmentSequenceFactory
    {
        $finMock = $this->createMock(FinFragment::class);
        $finMock->method('getValue')->willReturn(true);
        $finMock->method('getKey')->willReturn('finBit');

        $opcodeMock = $this->createMock(OpcodeFragment::class);
        $opcodeMock->method('getValue')->willReturn(Opcode::Text);
        $opcodeMock->method('getKey')->willReturn('opcode');

        $maskedMock = $this->createMock(MaskedFragment::class);
        $maskedMock->expects($this->once())->method('isMasked')->willReturn(false);

        $payloadLengthMock = $this->createMock(PayloadLengthFragment::class);

        $payloadLength16bitMock = $this->createMock(PayloadLength16BitFragment::class);
        $payloadLength16bitMock->expects($this->once())->method('isBypassable')->willReturn(true);

        $payloadLength64bitMock = $this->createMock(PayloadLength64BitFragment::class);
        $payloadLength64bitMock->expects($this->once())->method('isBypassable')->willReturn(true);

        $maskingKeyMock = $this->createMock(MaskingKeyFragment::class);
        $maskingKeyMock->expects($this->once())->method('isBypassable')->willReturn(!$maskedMock->isMasked());


        $payloadMock = $this->createMock(PayloadFragment::class);
        $payloadMock->method('getValue')->willReturn('something');
        $payloadMock->method('getKey')->willReturn('payload');
        $payloadMock->setPayloadLengthEntities($payloadLength64bitMock, $payloadLength16bitMock, $payloadLengthMock);
        $payloadMock->expects($this->exactly(2))->method('getPullLength')->willReturn(100);

        $fragmentCollection = [
            $finMock,
            $opcodeMock,
            $maskedMock,
            $payloadLengthMock,
            $payloadLength16bitMock,
            $payloadLength64bitMock,
            $maskingKeyMock,
            $payloadMock
        ];

        $sequencer = $this->createMock(FragmentSequenceFactory::class);
        $sequencer->expects($this->once())->method('getSequence')->willReturnCallback(function () use ($fragmentCollection) {
            foreach ($fragmentCollection as $fragment) {
                yield $fragment;
            }
        });

        return $sequencer;
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
        $factory->method('getSequence')->willReturnCallback(function () {
            yield new \stdClass();
        });

        return $factory;
    }
}
