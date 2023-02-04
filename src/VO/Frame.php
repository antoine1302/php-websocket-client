<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\VO;

use Totoro1302\PhpWebsocketClient\Enum\Opcode;

class Frame
{
    public function __construct(
        private readonly bool $finBit,
        private readonly Opcode $opcode,
        private readonly bool $masked,
        private readonly int $payloadLength,
        private readonly ?int $maskingKey,
        private readonly string $payload
    ) {
    }
    public function isFinal(): bool
    {
        return $this->finBit;
    }

    public function getOpcode(): Opcode
    {
        return $this->opcode;
    }

    public function isMasked(): bool
    {
        return $this->masked;
    }

    public function getPayloadLength(): int
    {
        return $this->payloadLength;
    }

    public function getMaskingKey(): ?int
    {
        return $this->maskingKey;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }
}
