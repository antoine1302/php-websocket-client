<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Iterator;

class MaskedPayloadIterator implements \Iterator
{
    private int $cursor = 0;

    public function __construct(
        private readonly string $payload,
        private readonly string $maskingKey
    ) {
    }

    public function current(): string
    {
        return $this->payload[$this->cursor] ^ $this->maskingKey[$this->cursor % 4];
    }

    public function key(): int
    {
        return $this->cursor;
    }

    public function next(): void
    {
        ++$this->cursor;
    }

    public function rewind(): void
    {
        $this->cursor = 0;
    }

    public function valid(): bool
    {
        return isset($this->payload[$this->cursor]);
    }
}
