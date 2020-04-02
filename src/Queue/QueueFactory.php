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

	private QueuesDataBag $queuesDataBag;

	private ConnectionFactory $connectionFactory;

	/**
	 * @var IQueue[]
	 */
	private array $queues;

	private QueueDeclarator $queueDeclarator;


	public function __construct(
		QueuesDataBag $queuesDataBag,
		ConnectionFactory $connectionFactory,
		QueueDeclarator $queueDeclarator
	)
	{
		$this->queuesDataBag = $queuesDataBag;
		$this->connectionFactory = $connectionFactory;
		$this->queueDeclarator = $queueDeclarator;
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
			$this->queueDeclarator->declareQueue($name);
		}

		return new Queue(
			$name,
			/*,
			$queueData['passive'],
			$queueData['durable'],
			$queueData['exclusive'],
			$queueData['autoDelete'],
			$queueData['noWait'],
			$queueData['arguments']*/
			$connection
		);
	}

}
