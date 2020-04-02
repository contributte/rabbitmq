<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ;

use Gamee\RabbitMQ\Producer\Exception\ProducerFactoryException;
use Gamee\RabbitMQ\Producer\Producer;
use Gamee\RabbitMQ\Producer\ProducerFactory;

/**
 * This package uses composer library bunny/bunny. For more information,
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
