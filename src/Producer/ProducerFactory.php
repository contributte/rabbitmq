<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Producer;

use Contributte\RabbitMQ\Exchange\ExchangeFactory;
use Contributte\RabbitMQ\Producer\Exception\ProducerFactoryException;
use Contributte\RabbitMQ\Queue\QueueFactory;
use Nette\SmartObject;

final class ProducerFactory
{

	use SmartObject;

	private ProducersDataBag $producersDataBag;

	private QueueFactory $queueFactory;

	private ExchangeFactory $exchangeFactory;

	/**
	 * @var Producer[]
	 */
	private array $producers = [];

	/**
	 * @var callable
	 */
	public $onCreated;

	public function __construct(
		ProducersDataBag $producersDataBag,
		QueueFactory $queueFactory,
		ExchangeFactory $exchangeFactory
	) {
		$this->producersDataBag = $producersDataBag;
		$this->queueFactory = $queueFactory;
		$this->exchangeFactory = $exchangeFactory;
	}


	/**
	 * @throws ProducerFactoryException
	 */
	public function getProducer(string $name): Producer
	{
		if (!isset($this->producers[$name])) {
			$this->producers[$name] = $this->create($name);
		}

		return $this->producers[$name];
	}


	/**
	 * @throws ProducerFactoryException
	 */
	private function create(string $name): Producer
	{
		try {
			$producerData = $this->producersDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {
			throw new ProducerFactoryException("Producer [$name] does not exist");
		}

		$exchange = null;
		$queue = null;

		if ($producerData['exchange']) {
			$exchange = $this->exchangeFactory->getExchange($producerData['exchange']);
		}

		if ($producerData['queue']) {
			$queue = $this->queueFactory->getQueue($producerData['queue']);
		}

		$producer = new Producer(
			$exchange,
			$queue,
			$producerData['contentType'],
			$producerData['deliveryMode']
		);

		$this->onCreated($name, $producer);

		return $producer;
	}

}
