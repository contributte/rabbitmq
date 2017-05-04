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
	 * @var Queue[]
	 */
	private $queues;


	/**
	 * @throws QueueFactoryException
	 */
	public function getQueue(Connection $connection, array $queueData): Queue
	{
		if (!isset($this->queues[$queueData['name']])) {
			$this->queues[$queueData['name']] = $this->create($connection, $queueData);
		}

		return $this->queues[$queueData['name']];
	}


	/**
	 * @throws QueueFactoryException
	 */
	public function create(Connection $connection, array $queueData): Queue
	{
		$queue = new Queue(
			$queueData['name'],
			$queueData['passive'],
			$queueData['durable'],
			$queueData['exclusive'],
			$queueData['autoDelete'],
			$queueData['noWait'],
			$queueData['arguments']
		);

		$connection->getChannel()->queueDeclare(
			$queueData['name'],
			$queueData['passive'],
			$queueData['durable'],
			$queueData['exclusive'],
			$queueData['autoDelete'],
			$queueData['noWait'],
			$queueData['arguments']
		);

		return $queue;
	}

}
