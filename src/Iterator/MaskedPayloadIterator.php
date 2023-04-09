<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Iterator;

class MaskedPayloadIterator implements \Iterator
{
    private int $cursor = 0;
    private readonly array $maskingKey;
    private readonly array $payload;

    public function __construct(
        string $payload,
        string $maskingKey
    ) {
        $this->payload = array_values(unpack('C*', $payload));
        $this->maskingKey = array_values(unpack('C4', $maskingKey));
    }

    public function current(): string
    {
        $byteXor = $this->payload[$this->cursor] ^ $this->maskingKey[$this->cursor % 4];
        return pack('C', $byteXor);
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
