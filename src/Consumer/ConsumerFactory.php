<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Consumer;

use Contributte\RabbitMQ\Consumer\Exception\ConsumerFactoryException;
use Contributte\RabbitMQ\Queue\QueueFactory;

final class ConsumerFactory
{

	private ConsumersDataBag $consumersDataBag;

	private QueueFactory $queueFactory;

	/**
	 * @var Consumer[]
	 */
	private array $consumers = [];


	public function __construct(
		ConsumersDataBag $consumersDataBag,
		QueueFactory $queueFactory
	) {
		$this->consumersDataBag = $consumersDataBag;
		$this->queueFactory = $queueFactory;
	}


	/**
	 * @throws ConsumerFactoryException
	 */
	public function getConsumer(string $name): Consumer
	{
		if (!isset($this->consumers[$name])) {
			$this->consumers[$name] = $this->create($name);
		}

		return $this->consumers[$name];
	}


	/**
	 * @throws ConsumerFactoryException
	 */
	private function create(string $name): Consumer
	{
		try {
			$consumerData = $this->consumersDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {
			throw new ConsumerFactoryException("Consumer [$name] does not exist");
		}

		$queue = $this->queueFactory->getQueue($consumerData['queue']);

		if (!is_callable($consumerData['callback'])) {
			throw new ConsumerFactoryException("Consumer [$name] has invalid callback");
		}

		$prefetchSize = null;
		$prefetchCount = null;

		if ($consumerData['qos']['prefetchSize'] !== null) {
			$prefetchSize = $consumerData['qos']['prefetchSize'];
		}

		if ($consumerData['qos']['prefetchCount'] !== null) {
			$prefetchCount = $consumerData['qos']['prefetchCount'];
		}

		return new Consumer(
			$name,
			$queue,
			$consumerData['callback'],
			$prefetchSize,
			$prefetchCount
		);
	}
}
