<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Handler;

use Totoro1302\PhpWebsocketClient\Enum\Opcode;
use Totoro1302\PhpWebsocketClient\Exception\WebSocketProtocolException;

class OpcodeFragmentHandler implements FragmentUnpackableAwareInterface
{
    private const BITMASK = 0x0F;
    private const KEY = 'opcode';
    private Opcode $value;
    public function unpack(string $binaryData): void
    {
        [$byte] = array_values(unpack('C', $binaryData));

        $opcode = $byte & self::BITMASK;

        $this->value = Opcode::tryFrom($opcode);

        if ($this->value === null) {
            throw new WebSocketProtocolException('Unpacking Opcode fragment failed');
        }
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getkey(): string
    {
        return self::KEY;
    }
}
