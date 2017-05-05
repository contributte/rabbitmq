<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ;

use Gamee\RabbitMQ\Consumer\Consumer;
use Gamee\RabbitMQ\Consumer\ConsumerFactory;
use Gamee\RabbitMQ\Consumer\Exception\ConsumerFactoryException;
use Gamee\RabbitMQ\Producer\Exception\ProducerFactoryException;
use Gamee\RabbitMQ\Producer\Producer;
use Gamee\RabbitMQ\Producer\ProducerFactory;

final class Client
{

	/**
	 * @var ProducerFactory
	 */
	private $producerFactory;

	/**
	 * @var ConsumerFactory
	 */
	private $consumerFactory;


	public function __construct(ProducerFactory $producerFactory, ConsumerFactory $consumerFactory)
	{
		$this->producerFactory = $producerFactory;
		$this->consumerFactory = $consumerFactory;
	}


	/**
	 * @throws ProducerFactoryException
	 */
	public function getProducer(string $name): Producer
	{
		return $this->producerFactory->getProducer($name);
	}


	/**
	 * @throws ConsumerFactoryException
	 */
	public function getConsumer(string $name): Consumer
	{
		return $this->consumerFactory->getConsumer($name);
	}

}
