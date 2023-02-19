<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient\Iterator;

use Totoro1302\PhpWebsocketClient\Service\Fragment\FragmentAwareInterface;
use Totoro1302\PhpWebsocketClient\Service\Fragment\FragmentBypassableAwareInterface;

class FragmentFilterIterator extends \FilterIterator
{
    public function accept(): bool
    {
        if (!$this->current() instanceof FragmentBypassableAwareInterface) {
            return true;
        }

        return !$this->current()->isBypassable();
    }

    public function current(): FragmentAwareInterface
    {
        return parent::current();
    }
}
