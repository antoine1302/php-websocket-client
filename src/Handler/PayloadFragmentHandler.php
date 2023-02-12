<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

class PayloadFragmentHandler implements FragmentUnpackableAwareInterface, FragmentPullableAwareInterface, FragmentStorableAwareInterface
{
    private const KEY = 'payload';
    private string $value;
    private ?array $payloadLengthFragments;

    public function unpack(string $binaryData): void
    {
        $this->value = $binaryData;
    }

    public function getLength(): ?int
    {
        foreach ($this->payloadLengthFragments as $fragment) {
            if (
                $fragment instanceof FragmentUnpackableAwareInterface
                && $fragment->getValue() === null
            ) {
                continue;
            }
            return $fragment->getValue();
        }

        return null;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getKey(): string
    {
        return self::KEY;
    }

    public function setPayloadLengthFragments(...$args): void
    {
        $this->payloadLengthFragments = $args;
    }
}
