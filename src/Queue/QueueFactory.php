<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Queue;

use Gamee\RabbitMQ\Connection\Connection;
use Gamee\RabbitMQ\Queue\Exception\QueueFactoryException;

final class QueueFactory
{

	/**
	 * @var QueuesDataBag
	 */
	private $queuesDataBag;

	/**
	 * @var Queue[]
	 */
	private $queues;


	public function __construct(QueuesDataBag $queuesDataBag)
	{
		$this->queuesDataBag = $queuesDataBag;
	}


	/**
	 * @throws QueueFactoryException
	 */
	public function getQueue(string $name, Connection $connection): Queue
	{
		if (!isset($this->queues[$name])) {
			$this->queues[$name] = $this->create($name, $connection);
		}

		return $this->queues[$name];
	}


	/**
	 * @throws QueueFactoryException
	 */
	private function create(string $name, Connection $connection): Queue
	{
		try {
			$queueData = $this->queuesDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {

			throw new QueueFactoryException("Queue [$name] does not exist");
		}

		$connection->getChannel()->queueDeclare(
			$name,
			$queueData['passive'],
			$queueData['durable'],
			$queueData['exclusive'],
			$queueData['autoDelete'],
			$queueData['noWait'],
			$queueData['arguments']
		);

		return new Queue(
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
