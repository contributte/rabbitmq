<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Queue;

use Contributte\RabbitMQ\Connection\ConnectionFactory;
use Contributte\RabbitMQ\Queue\Exception\QueueFactoryException;

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
			throw new QueueFactoryException(sprintf('Queue [%s] does not exist', $name));
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
