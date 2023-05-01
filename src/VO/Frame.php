<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\VO;

use Totoro1302\PhpWebsocketClient\Enum\Opcode;

readonly class Frame
{
    public function __construct(
        private bool $finBit,
        private Opcode $opcode,
        private string $payload
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

    public function getPayload(): string
    {
        return $this->payload;
    }
}
