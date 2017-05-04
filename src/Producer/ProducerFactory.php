<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Producer;

use Gamee\RabbitMQ\Connection\ConnectionFactory;
use Gamee\RabbitMQ\Producer\Exception\ProducerFactoryException;
use Gamee\RabbitMQ\Queue\QueueFactory;

final class ProducerFactory
{

	/**
	 * @var ProducersDataBag
	 */
	private $producersDataBag;

	/**
	 * @var ConnectionFactory
	 */
	private $connectionFactory;

	/**
	 * @var QueueFactory
	 */
	private $queueFactory;

	/**
	 * @var Producer[]
	 */
	private $producers;


	public function __construct(
		ProducersDataBag $producersDataBag,
		ConnectionFactory $connectionFactory,
		QueueFactory $queueFactory
	) {
		$this->producersDataBag = $producersDataBag;
		$this->connectionFactory = $connectionFactory;
		$this->queueFactory = $queueFactory;
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
	public function create(string $name): Producer
	{
		try {
			$producerData = $this->producersDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {

			throw new ProducerFactoryException("Producer [$name] does not exist");
		}

		$connection = $this->connectionFactory->getConnection($producerData['connection']);
		$queue = $this->queueFactory->getQueue($connection, $producerData['queue']);

		return new Producer(
			$connection,
			$producerData['exchange'],
			$queue,
			$producerData['contentType'],
			$producerData['deliveryMode']
		);
	}

}
