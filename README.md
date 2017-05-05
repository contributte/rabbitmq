Example configuration:

```
services:
	- App\Queue\RabbitMq\Consumer\TestConsumer


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

	consumers:
		testConsumer:
			queue: testQueue
			callback: [@App\Queue\RabbitMq\Consumer\TestConsumer, consume]
```
