<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Queue;

use Contributte\RabbitMQ\Connection\ConnectionFactory;
use Contributte\RabbitMQ\Queue\Exception\QueueFactoryException;

final class QueueDeclarator
{

	public function __construct(
		private ConnectionFactory $connectionFactory,
		private QueuesDataBag $queuesDataBag
	) {
	}

	public function declareQueue(string $name): void
	{
		try {
			$queueData = $this->queuesDataBag->getDataByKey($name);
		} catch (\InvalidArgumentException) {
			throw new QueueFactoryException("Queue [$name] does not exist");
		}

		$connection = $this->connectionFactory->getConnection($queueData['connection']);

		$connection->getChannel()->queueDeclare(
			$name,
			$queueData['passive'],
			$queueData['durable'],
			$queueData['exclusive'],
			$queueData['autoDelete'],
			$queueData['noWait'],
			$queueData['arguments']
		);
	}
}
