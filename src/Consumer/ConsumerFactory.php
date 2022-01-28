<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Consumer;

use Contributte\RabbitMQ\Consumer\Exception\ConsumerFactoryException;
use Contributte\RabbitMQ\LazyDeclarator;
use Contributte\RabbitMQ\Queue\QueueFactory;

final class ConsumerFactory
{
	/**
	 * @var Consumer[]
	 */
	private array $consumers = [];


	public function __construct(
		private ConsumersDataBag $consumersDataBag,
		private QueueFactory $queueFactory,
		private LazyDeclarator $lazyDeclarator,
	) {
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
			$consumerData = $this->consumersDataBag->getDataByKey($name);
		} catch (\InvalidArgumentException) {
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

		if (is_array($consumerData['bulk']) && $consumerData['bulk']['size']) {
			return new BulkConsumer(
				$this->lazyDeclarator,
				$name,
				$queue,
				$consumerData['callback'],
				$prefetchSize,
				$prefetchCount,
				(int) $consumerData['bulk']['size'],
				(int) $consumerData['bulk']['timeout']
			);
		}

		return new Consumer(
			$this->lazyDeclarator,
			$name,
			$queue,
			$consumerData['callback'],
			$prefetchSize,
			$prefetchCount
		);
	}
}
