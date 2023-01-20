<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Totoro1302\PhpWebsocketClient\Service\StreamSocketHandler;
use Totoro1302\PhpWebsocketClient\Service\Hansdshake\HeadersBuilder;
use Totoro1302\PhpWebsocketClient\Service\Hansdshake\KeyGenerator;

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ClientConfigInterface $clientConfig;
    private StreamSocketHandler $socketHandler;

    public function __construct(
        ClientConfigInterface $clientConfig,
        StreamSocketHandler $socketHandler,
    ) {
        $this->clientConfig = $clientConfig;
        $this->socketHandler = $socketHandler;
    }

    public function __destruct()
    {
    }

    public function connect(): void
    {
        $uri = $this->clientConfig->getUri();

        $this->socketHandler->initiate($uri);

    }

    public function disconnect(): void
    {
    }

    public function isConnected(): bool
    {
        return true;
    }
}
