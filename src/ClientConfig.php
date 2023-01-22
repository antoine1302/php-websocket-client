<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient;

class ClientConfig implements ClientConfigInterface
{
    public function __construct(
        private readonly string $name,
        private readonly string $uri,
        private readonly ?int $connectionTimeout,
        private readonly ?bool $isPersistent,
        private readonly ?array $subProtocols
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getConnectionTimeout(): ?int
    {
        return $this->connectionTimeout;
    }

    public function isPersistent(): ?bool
    {
        return $this->isPersistent;
    }

    public function getSubProtocols(): ?array
    {
        return $this->subProtocols;
    }
}
