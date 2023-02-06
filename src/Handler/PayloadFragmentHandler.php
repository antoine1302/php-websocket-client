<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

class PayloadFragmentHandler implements FragmentUnpackableAwareInterface, FragmentLengthAwareInterface
{
    private ?array $payloadLengthFragments;

    public function unpack(string $binaryData): void
    {
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

    public function getValue()
    {

    }

    public function setPayloadLengthFragments(...$args): void
    {
        $this->payloadLengthFragments = $args;
    }
}
