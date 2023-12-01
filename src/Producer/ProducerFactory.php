<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Producer;

use Contributte\RabbitMQ\Exchange\ExchangeFactory;
use Contributte\RabbitMQ\Producer\Exception\ProducerFactoryException;
use Contributte\RabbitMQ\Queue\QueueFactory;

final class ProducerFactory
{

	/** @var callable[] */
	public array $createdCallbacks = [];

	private ProducersDataBag $producersDataBag;

	private QueueFactory $queueFactory;

	private ExchangeFactory $exchangeFactory;

	/** @var Producer[] */
	private array $producers = [];

	public function __construct(
		ProducersDataBag $producersDataBag,
		QueueFactory $queueFactory,
		ExchangeFactory $exchangeFactory
	)
	{
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

	public function addOnCreatedCallback(callable $callback): void
	{
		$this->createdCallbacks[] = $callback;
	}

	/**
	 * @throws ProducerFactoryException
	 */
	private function create(string $name): Producer
	{
		try {
			$producerData = $this->producersDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {
			throw new ProducerFactoryException(sprintf('Producer [%s] does not exist', $name));
		}

		$exchange = null;
		$queue = null;

		if (isset($producerData['exchange'])) {
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

		foreach ($this->createdCallbacks as $callback) {
			($callback)($name, $producer);
		}

		return $producer;
	}

}
