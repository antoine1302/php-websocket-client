<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient;

class ClientConfig implements ClientConfigInterface
{
    private const CONNECTION_TIMEOUT_DEFAULT = 5;
    private const IS_PERSISTENT_DEFAULT = false;
    public function __construct(
        private readonly string $name,
        private readonly string $uri,
        private readonly int $connectionTimeout,
        private readonly bool $isPersistent,
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

    public function getConnectionTimeout(): int
    {
        return $this->connectionTimeout ?? self::CONNECTION_TIMEOUT_DEFAULT;
    }

    public function isPersistent(): bool
    {
        return $this->isPersistent ?? self::IS_PERSISTENT_DEFAULT;
    }

    public function getSubProtocols(): ?array
    {
        return $this->subProtocols;
    }
}
