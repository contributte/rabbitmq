<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Consumer;

use Gamee\RabbitMQ\Consumer\Exception\ConsumerFactoryException;
use Gamee\RabbitMQ\Queue\QueueFactory;

final class ConsumerFactory
{

	/**
	 * @var ConsumersDataBag
	 */
	private $consumersDataBag;

	/**
	 * @var QueueFactory
	 */
	private $queueFactory;

	/**
	 * @var Consumer[]
	 */
	private $consumers = [];


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
