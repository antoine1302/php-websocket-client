<?php

declare(strict_types=1);

namespace Totoro1302\PhpWebsocketClient;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Totoro1302\PhpWebsocketClient\Exception\ClientException;
use Totoro1302\PhpWebsocketClient\Service\Hansdshake\{HeadersBuilder,KeyGenerator,KeyValidator};
use Totoro1302\PhpWebsocketClient\Service\StreamSocketHandler;

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ClientConfigInterface $clientConfig;
    private UriFactoryInterface $uriFactory;
    private StreamSocketHandler $socketHandler;
    private KeyGenerator $keyGenerator;
    private KeyValidator $keyValidator;
    private HeadersBuilder $headersBuilder;

    public function __construct(
        ClientConfigInterface $clientConfig,
        UriFactoryInterface $uriFactoryInterface,
        StreamSocketHandler $socketHandler,
        KeyGenerator $keyGenerator,
        KeyValidator $keyValidator
    ) {
        $this->clientConfig = $clientConfig;
        $this->uriFactory = $uriFactoryInterface;
        $this->socketHandler = $socketHandler;
        $this->keyGenerator = $keyGenerator;
        $this->keyValidator = $keyValidator;
    }

    public function __destruct()
    {
    }

    public function connect(): void
    {
        $uri = $this->uriFactory->createUri($this->clientConfig->getUri());

        $this->socketHandler->open(
            $uri,
            $this->clientConfig->getConnectionTimeout(),
            $this->clientConfig->isPersistent()
        );

        $clientKey = $this->keyGenerator->generate();
        $clientHeaders = $this->headersBuilder->build(
            $uri, 
            $clientKey, 
            $this->clientConfig->getSubProtocols(), 
            $this->clientConfig->getOrigin()
        );

        $this->socketHandler->write($clientHeaders);

        $serverHeaders = $this->socketHandler->read();

        if(false === $this->keyValidator->validate($clientKey, $serverHeaders)){
            throw new ClientException("Unable to validate key from server");
        }
    }

    public function disconnect(): void
    {
    }

    public function isConnected(): bool
    {
        return true;
    }
}
