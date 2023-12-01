<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ;

use Contributte\RabbitMQ\Producer\Exception\ProducerFactoryException;
use Contributte\RabbitMQ\Producer\Producer;
use Contributte\RabbitMQ\Producer\ProducerFactory;

/**
 * This package uses composer library bunny/bunny. For more information,
 *
 * @see https://github.com/jakubkulhan/bunny
 */
final class Client
{

	private ProducerFactory $producerFactory;

	public function __construct(ProducerFactory $producerFactory)
	{
		$this->producerFactory = $producerFactory;
	}

	/**
	 * @throws ProducerFactoryException
	 */
	public function getProducer(string $name): Producer
	{
		return $this->producerFactory->getProducer($name);
	}

}
