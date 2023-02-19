<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Service\Fragment;

use Totoro1302\PhpWebsocketClient\Enum\Opcode;
use Totoro1302\PhpWebsocketClient\Exception\WebSocketProtocolException;

class OpcodeFragment implements FragmentAwareInterface, FragmentStorableAwareInterface
{
    private const BITMASK = 0x0F;
    private const KEY = 'opcode';
    private Opcode $value;

    public function load(string $binaryData)
    {
        [$byte] = array_values(unpack('C', $binaryData));

        $opcode = $byte & self::BITMASK;

        $this->value = Opcode::tryFrom($opcode);

        if ($this->value === null) {
            throw new WebSocketProtocolException('Unpacking Opcode fragment failed');
        }
    }

    public function getKey(): string
    {
        return self::KEY;
    }

    public function getValue(): Opcode
    {
        return $this->value;
    }
}
