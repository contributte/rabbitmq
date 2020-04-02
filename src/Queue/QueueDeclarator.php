<?php

declare(strict_types=1);

namespace Gamee\RabbitMQ\Queue;

use Gamee\RabbitMQ\Connection\ConnectionFactory;
use Gamee\RabbitMQ\Queue\Exception\QueueFactoryException;

final class QueueDeclarator
{

	private QueuesDataBag $queuesDataBag;

	private ConnectionFactory $connectionFactory;


	public function __construct(ConnectionFactory $connectionFactory, QueuesDataBag $queuesDataBag)
	{
		$this->queuesDataBag = $queuesDataBag;
		$this->connectionFactory = $connectionFactory;
	}


	public function declareQueue(string $name): void
	{
		try {
			$queueData = $this->queuesDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {
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
