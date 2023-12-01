<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Queue;

use Contributte\RabbitMQ\Connection\ConnectionFactory;
use Contributte\RabbitMQ\Connection\Exception\ConnectionFactoryException;
use Contributte\RabbitMQ\Queue\Exception\QueueFactoryException;

final class QueueFactory
{

	private QueuesDataBag $queuesDataBag;

	private ConnectionFactory $connectionFactory;

	/** @var IQueue[] */
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
			throw new QueueFactoryException(sprintf('Queue [%s] does not exist', $name));
		}

		$connection = $this->connectionFactory->getConnection($queueData['connection']);

		if (isset($queueData['autoCreate']) && $queueData['autoCreate'] === true) {
			$this->queueDeclarator->declareQueue($name);
		}

		return new Queue(
			$name,
			$connection
		);
	}

}
