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

readonly class Writer
{
    public function write($socket, array $frameCollection, bool $isMasked): void
    {
        if (!is_resource($socket)) {
            throw new StreamSocketException('Try to use a non-resource type');
        }

        foreach ($frameCollection as $frame) {
            if (!$frame instanceof Frame) {
                throw new \LogicException("Require instance of Frame class");
            }

            $packFormat = '';
            $fragmentCollection = [];

            $finBit = $frame->isFinal() ? FinFragment::BITMASK : 0;
            $fragmentCollection[] = $finBit | $frame->getOpcode()->value;

            $masked = $isMasked ? MaskedFragment::BITMASK : 0;
            $payloadLength = strlen($frame->getPayload());
            $payloadLengthIndicator = match (true) {
                ($payloadLength < PayloadLength16BitFragment::PAYLOAD_INDEX) => $payloadLength,
                ($payloadLength < PayloadLength16BitFragment::PAYLOAD_THRESHOLD) => PayloadLength16BitFragment::PAYLOAD_INDEX,
                default => PayloadLength64BitFragment::PAYLOAD_INDEX
            };
            $fragmentCollection[] = $masked | $payloadLengthIndicator;

            $packFormat .= 'C2';

            if ($payloadLengthIndicator === PayloadLength16BitFragment::PAYLOAD_INDEX) {
                $fragmentCollection[] = $payloadLength;
                $packFormat .= 'n';
            } elseif ($payloadLengthIndicator === PayloadLength64BitFragment::PAYLOAD_INDEX) {
                $fragmentCollection[] = $payloadLength;
                $packFormat .= 'J';
            }

            $payload = $frame->getPayload();
            if ($isMasked) {
                $maskingKey = random_bytes(4);
                $fragmentCollection[] = $maskingKey;
                $packFormat .= 'N';

                $payload = '';
                foreach (new MaskedPayloadIterator($frame->getPayload(), $maskingKey) as $maskedByte) {
                    $payload .= $maskedByte;
                }
            }

            $binaryData = pack($packFormat, ...$fragmentCollection);
            $binaryData .= $payload;
            $this->send($socket, $binaryData);
        }
    }

    private function send($stream, $data): void
    {
        stream_socket_sendto($stream, $data);
    }
}
