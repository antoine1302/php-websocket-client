<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Fragment;

use Totoro1302\PhpWebsocketClient\Exception\WebSocketProtocolException;
use Totoro1302\PhpWebsocketClient\Iterator\MaskedPayloadIterator;

class PayloadFragment implements FragmentAwareInterface, FragmentPullableAwareInterface, FragmentStorableAwareInterface
{
    private const KEY = 'payload';
    private string $value;
    private array $entities;

    public function __construct(private readonly MaskingKeyFragment $fragment)
    {
    }

    public function load(string $binaryData): void
    {
        $this->value = $binaryData;
    }

    public function getPullLength(): int
    {
        foreach ($this->entities as $fragment) {
            if ($fragment->getPayloadLength() === null) {
                continue;
            }
            return $fragment->getPayloadLength();
        }

        throw new WebSocketProtocolException("Unable to get payload length");
    }

    public function setPayloadLengthEntities(...$entities)
    {
        foreach ($entities as $fragment) {
            if (!$fragment instanceof FragmentPayloadLengthAwareInterface) {
                throw new \InvalidArgumentException('Entity must implement FragmentPayloadLengthAwareInterface');
            }
        }

        $this->entities = $entities;
    }

    public function getKey(): string
    {
        return self::KEY;
    }

    public function getValue(): string
    {
        if ($this->fragment->getMaskingKey() === null) {
            return $this->value;
        }

        $maskingKey = $this->fragment->getMaskingKey();

        $payloadUnmasked = '';

        foreach (new MaskedPayloadIterator($this->value, $maskingKey) as $unmaskedByte) {
            $payloadUnmasked .= $unmaskedByte;
        }

        return $payloadUnmasked;
    }
}
