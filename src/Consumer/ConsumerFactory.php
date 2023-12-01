<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Consumer;

use Contributte\RabbitMQ\Consumer\Exception\ConsumerFactoryException;
use Contributte\RabbitMQ\Queue\QueueFactory;

final class ConsumerFactory
{

	private ConsumersDataBag $consumersDataBag;

	private QueueFactory $queueFactory;

	/** @var Consumer[] */
	private array $consumers = [];

	public function __construct(
		ConsumersDataBag $consumersDataBag,
		QueueFactory $queueFactory
	)
	{
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
			throw new ConsumerFactoryException(sprintf('Consumer [%s] does not exist', $name));
		}

		$queue = $this->queueFactory->getQueue($consumerData['queue']);

		if (!is_callable($consumerData['callback'])) {
			throw new ConsumerFactoryException(sprintf('Consumer [%s] has invalid callback', $name));
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
			$name,
			$queue,
			$consumerData['callback'],
			$prefetchSize,
			$prefetchCount
		);
	}

}
