<?php

trait FragmentBypassableAwareTrait
{
    private \Closure $callback;

    public function isBypassable(): bool
    {
        return ($this->callback)($this->getBypassCallbackArgs());
    }

    public function setBypassCallback(\Closure $closure): void
    {
        $this->callback = $closure;
    }

    abstract protected function getBypassCallbackArgs();
}
