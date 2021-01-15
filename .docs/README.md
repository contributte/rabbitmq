# RabbitMQ

Nette extension for RabbitMQ (using composer package [jakubkulhan/bunny](https://github.com/jakubkulhan/bunny))

## Installation

```
composer require contributte/rabbitmq
```

## Extension registration

config.neon:

```
extensions:
	rabbitmq: Contributte\RabbitMQ\DI\RabbitMQExtension
```

## Example configuration

```
services:
	- TestConsumer

rabbitmq:
	connections:
		default:
			user: guest
			password: guest
			host: localhost
			port: 5672
			lazy: false

	queues:
		testQueue:
			connection: default
			# force queue declare on first queue operation during request
			# autoCreate: true 

	exchanges:
		testExchange:
			connection: default
			type: fanout
			queueBindings:
				testQueue:
					routingKey: testRoutingKey
			# force exchange declare on first exchange operation during request
			# autoCreate: true

	producers:
		testProducer:
			exchange: testExchange
			# queue: testQueue
			contentType: application/json
			deliveryMode: 2 # Producer::DELIVERY_MODE_PERSISTENT

	consumers:
		testConsumer:
			queue: testQueue
			callback: [@TestConsumer, consume]
			qos:
				prefetchSize: 0
				prefetchCount: 5

# to enable tracy bar
tracy:
    bar:
        - Contributte\RabbitMQ\Diagnostics\BarPanel
```

## Declaring Queues and Exchanges

Since v3.0, all queues and exchanges are by default declared on demand using the console command: 

```
php index.php rabbitmq:declareQueuesAndExchanges
```

It's intended to be a part of the deploy process to make sure all the queues and exchanges are prepared for use.

If you need to override this behavior (for example only declare queues that are used during a request and nothing else), 
just add the `autoCreate: true` parameter to queue or exchange of your choice.

You may also want to declare the queues and exchanges via rabbitmq management interface or a script but if you fail to 
do so, don't run the declare console command and don't specify `autoCreate: true`, exceptions will be thrown 
when accessing undeclared queues/exchanges.

## Publishing messages

services.neon:

```
services:
	- TestQueue(@Contributte\RabbitMQ\Client::getProducer(testProducer))
```

TestQueue.php:

```php
<?php

declare(strict_types=1);

use Contributte\RabbitMQ\Producer\Producer;

final class TestQueue
{

	/**
	 * @var Producer
	 */
	private $testProducer;


	public function __construct(Producer $testProducer)
	{
		$this->testProducer = $testProducer;
	}


	public function publish(string $message): void
	{
		$json = json_encode(['message' => $message]);
		$headers = [];

		$this->testProducer->publish($json, $headers);
	}

}
```

## Consuming messages

Your consumer callback has to return a confirmation that particular message has been acknowledges (or different states - unack, reject).

TestConsumer.php

```php
<?php

declare(strict_types=1);

use Bunny\Message;
use Contributte\RabbitMQ\Consumer\IConsumer;

final class TestConsumer implements IConsumer
{

	public function consume(Message $message): int
	{
		$messageData = json_decode($message->content);

		$headers = $message->headers;

		/**
		 * @todo Some logic here...
		 */

		return IConsumer::MESSAGE_ACK; // Or ::MESSAGE_NACK || ::MESSAGE_REJECT
	}

}
```

## Running a consumer trough CLI

There are two consumer commands prepared. `rabbitmq:consumer` wiil consume messages for specified amount of time (in seconds), to run indefinitely skip this parameter. Following command will be consuming messages for one hour:

```
php index.php rabbitmq:consumer testConsumer 3600
```

Following command will be consuming messages indefinitely:

```
php index.php rabbitmq:consumer testConsumer
```


`rabbitmq:staticConsumer` will consume particular amount of messages. Following example will consume just 20 messages:

```
php index.php rabbitmq:staticConsumer testConsumer 20
```
