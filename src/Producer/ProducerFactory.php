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


	public function __construct(
		ProducersDataBag $producersDataBag,
		ConnectionFactory $connectionFactory
	) {
		$this->producersDataBag = $producersDataBag;
		$this->connectionFactory = $connectionFactory;
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

		return new Producer(
			$connection,
			$producerData['exchange'],
			$producerData['queue'],
			$producerData['contentType'],
			$producerData['deliveryMode']
		);
	}

}
