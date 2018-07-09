<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Queue;

use Gamee\RabbitMQ\Connection\ConnectionFactory;
use Gamee\RabbitMQ\Connection\Exception\ConnectionFactoryException;
use Gamee\RabbitMQ\Queue\Exception\QueueFactoryException;

final class QueueFactory
{

	/**
	 * @var QueuesDataBag
	 */
	private $queuesDataBag;

	/**
	 * @var ConnectionFactory
	 */
	private $connectionFactory;

	/**
	 * @var IQueue[]
	 */
	private $queues;


	public function __construct(QueuesDataBag $queuesDataBag, ConnectionFactory $connectionFactory)
	{
		$this->queuesDataBag = $queuesDataBag;
		$this->connectionFactory = $connectionFactory;
	}


	/**
	 * @throws QueueFactoryException
	 */
	public function getQueue(string $name): IQueue
	{
		if (!isset($this->queues[$name])) {
			$this->queues[$name] = $this->create($name);
		}

		return $this->queues[$name];
	}


	/**
	 * @throws QueueFactoryException
	 * @throws ConnectionFactoryException
	 */
	private function create(string $name): IQueue
	{
		try {
			$queueData = $this->queuesDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {

			throw new QueueFactoryException("Queue [$name] does not exist");
		}

		// (ConnectionFactoryException)
		$connection = $this->connectionFactory->getConnection($queueData['connection']);

		if ($queueData['autoCreate']) {
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

		return new Queue(
			$name,
			$connection,
			$queueData['passive'],
			$queueData['durable'],
			$queueData['exclusive'],
			$queueData['autoDelete'],
			$queueData['noWait'],
			$queueData['arguments']
		);
	}

}
