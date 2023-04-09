<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Frame;

use Totoro1302\PhpWebsocketClient\Exception\StreamSocketException;
use Totoro1302\PhpWebsocketClient\Iterator\MaskedPayloadIterator;
use Totoro1302\PhpWebsocketClient\Service\Fragment\FinFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\MaskedFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadLength16BitFragment;
use Totoro1302\PhpWebsocketClient\Service\Fragment\PayloadLength64BitFragment;
use Totoro1302\PhpWebsocketClient\VO\Frame;

class Writer
{
    private string $packFormat = '';
    private array $fragmentCollection = [];

    public function write($socket, array $frameCollection, bool $isMasked): void
    {
        if (!is_resource($socket)) {
            throw new StreamSocketException('Try to use a non-resource type');
        }

        foreach ($frameCollection as $frame) {
            if (!$frame instanceof Frame) {
                throw new \LogicException("Require instance of Frame class");
            }

            $this->initialize();

            $this->buildFinAndOpcodeFragment($frame);
            [$payloadLengthIndicator, $payloadLength] = $this->buildMaskedAndPayloadFragment($frame, $isMasked);

            if ($payloadLengthIndicator === PayloadLength16BitFragment::PAYLOAD_INDEX) {
                $this->buildPayloadLengthExtended16BitFragment($payloadLength);
            } elseif ($payloadLengthIndicator === PayloadLength64BitFragment::PAYLOAD_INDEX) {
                $this->buildPayloadLengthExtended64BitFragment($payloadLength);
            }

            $maskingKey = $isMasked ? random_bytes(4) : null;

            $binaryData = pack($this->packFormat, ...$this->fragmentCollection);
            $binaryData .= $maskingKey ?? '';
            $binaryData .= $this->buildPayload($frame, $maskingKey);;
            $this->send($socket, $binaryData);
        }
    }

    private function initialize(): void
    {
        $this->packFormat = '';
        $this->fragmentCollection = [];
    }

    private function buildFinAndOpcodeFragment(Frame $frame): void
    {
        $finBit = $frame->isFinal() ? FinFragment::BITMASK : 0;
        $this->fragmentCollection[] = $finBit | $frame->getOpcode()->value;
        $this->packFormat .= 'C';
    }

    private function buildMaskedAndPayloadFragment(Frame $frame, bool $isMasked): array
    {
        $masked = $isMasked ? MaskedFragment::BITMASK : 0;
        $payloadLength = strlen($frame->getPayload());
        $payloadLengthIndicator = match (true) {
            ($payloadLength < PayloadLength16BitFragment::PAYLOAD_INDEX) => $payloadLength,
            ($payloadLength < PayloadLength16BitFragment::PAYLOAD_THRESHOLD) => PayloadLength16BitFragment::PAYLOAD_INDEX,
            default => PayloadLength64BitFragment::PAYLOAD_INDEX
        };
        $this->fragmentCollection[] = $masked | $payloadLengthIndicator;
        $this->packFormat .= 'C';

        return [$payloadLengthIndicator, $payloadLength];
    }

    private function buildPayloadLengthExtended16BitFragment(int $payloadLength): void
    {
        $this->fragmentCollection[] = $payloadLength;
        $this->packFormat .= 'n';
    }

    private function buildPayloadLengthExtended64BitFragment(int $payloadLength): void
    {
        $this->fragmentCollection[] = $payloadLength;
        $this->packFormat .= 'J';
    }

    private function buildPayload(Frame $frame, ?string $maskingKey): string
    {
        if ($maskingKey === null) {
            return $frame->getPayload();
        }

        $payload = '';
        foreach (new MaskedPayloadIterator($frame->getPayload(), $maskingKey) as $maskedByte) {
            $payload .= $maskedByte;
        }

        return $payload;
    }

    private function send($socket, $data): void
    {
        stream_socket_sendto($socket, $data);
    }
}
