# PHP Websocket Client

## What is it?

Basic implementation of PHP Websocket client that follow RFC6455

## Installation

If you wish to install it in your project, require it via composer:

```bash
composer require totoro1302/php-websocket-client
```

## Tests
### PHPUnit
* Coverage must be 100% on `src/`
## Stack description
### PHP
* PHP >= 8.2 is required
### Extensions
* extension pcntl
### Dependencies
* psr/log
* nyholm/psr7
* psr/http-factory

## Run test
### Run PHP unit tests
```bash
bin/test.sh unit
```
### Run code sniffer
```bash
bin/test.sh static
```
### Run PHPStan static
```bash
bin/test.sh static-analyze
```
### Run PHP 8.2 compatibility
```bash
bin/test.sh php82-compatibility
```
### Run code smell fix
```bash
bin/test.sh static-fix
```

## Usage

```php
<?php

use Totoro1302\PhpWebsocketClient\Client;use Totoro1302\PhpWebsocketClient\ClientConfig;

$clientConfig = new ClientConfig(
'myWsClient', // give a name to the connection (mandatory)
'wss://some-ws-srv.com', // websocket server address (mandatory)
10, // connection timeout (optional)
'some_origin.com', // you can specify an origin (optional)
false, // persistent (optional)
['user-agent' => 'myAgent'] // add additional headers (optional)
);

$client = new Client($clientConfig);
$client->connect();
$client->push('Hello world');

while ($client->isRunning()) {
    $data = $client->pull();
    // Do something with the data here
    usleep(mt_rand(50000, 100000)); // You can eventually add some timeout pull delay if needed
}
```

### Auto Respond

The client handles ping/pong logic on both sides. It means it automatically respond to ping frame by a pong frame, and client also
send ping frame to the server to check the server is always alive.
