<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient;

interface ClientConfigInterface
{
    public function getName(): string;
    public function getUri(): string;
    public function getConnectionTimeout(): ?int;
    public function getOrigin(): ?string;
    public function isPersistent(): ?bool;
    public function getSubProtocols(): ?array;
    
}
