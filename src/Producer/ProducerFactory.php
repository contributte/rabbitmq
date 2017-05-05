<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Producer;

use Gamee\RabbitMQ\Exchange\ExchangeFactory;
use Gamee\RabbitMQ\Producer\Exception\ProducerFactoryException;
use Gamee\RabbitMQ\Queue\QueueFactory;

final class ProducerFactory
{

	/**
	 * @var ProducersDataBag
	 */
	private $producersDataBag;

	/**
	 * @var QueueFactory
	 */
	private $queueFactory;

	/**
	 * @var ExchangeFactory
	 */
	private $exchangeFactory;

	/**
	 * @var Producer[]
	 */
	private $producers;


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

		$exchange = NULL;
		$queue = NULL;

		if ($producerData['exchange']) {
			$exchange = $this->exchangeFactory->getExchange($producerData['exchange']);
		}

		if ($producerData['queue']) {
			$queue = $this->queueFactory->getQueue($producerData['queue']);
		}

		return new Producer(
			$exchange,
			$queue,
			$producerData['contentType'],
			$producerData['deliveryMode']
		);
	}

}
