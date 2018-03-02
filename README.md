[![Latest Stable Version](https://poser.pugx.org/gamee/nette-rabbitmq/v/stable)](https://packagist.org/packages/gamee/nette-rabbitmq)
[![License](https://poser.pugx.org/gamee/nette-rabbitmq/license)](https://packagist.org/packages/gamee/nette-rabbitmq)
[![Total Downloads](https://poser.pugx.org/gamee/nette-rabbitmq/downloads)](https://packagist.org/packages/gamee/nette-rabbitmq)

# Nette RabbitMQ

Nette extension for RabbitMQ (using composer package [jakubkulhan/bunny](https://github.com/jakubkulhan/bunny))

## Example setup

### Downloading composer package

```
composer require gamee/nette-rabbitmq
```

### Extension registration

config.neon:

```
extensions:
	rabbitmq: Gamee\RabbitMQ\DI\RabbitMQExtension
```

### Example configuration

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

	queues:
		testQueue:
			connection: default

	exchanges:
		testExchange:
			type: fanout
			queueBindings:
				testQueue:

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
```

### Publishing messages

Note: Queue will be created automatically after publishing first message. 

services.neon:

```
services:
	- TestQueue(@Gamee\RabbitMQ\Client::getProducer(testProducer))
```

TestQueue.php:

```php
<?php

declare(strict_types=1);

use Gamee\RabbitMQ\Producer\Producer;

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

### Consuming messages

Your consumer callback has to return a confirmation that particular message has been acknowledges (or different states - unack, reject).

TestConsumer.php

```php
<?php

declare(strict_types=1);

use Bunny\Message;
use Gamee\RabbitMQ\Consumer\IConsumer;

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

### Running a consumer trough CLI

There are two consumer commands prepared. `rabbitmq:consumer` wiil consume messages for specified amount of time (in seconds). Following command wiil be consuming messages for one hour:

```
php index.php rabbitmq:consumer testConsumer 3600
```

`rabbitmq:staticConsumer` will consume particular amount of messages. Following example will consume just 20 messages:

```
php index.php rabbitmq:staticConsumer testConsumer 20
```
