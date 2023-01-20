<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient;

class ClientConfig implements ClientConfigInterface
{
    private string $uri;

    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}
