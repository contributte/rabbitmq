<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ;

use Gamee\RabbitMQ\Producer\Producer;
use Gamee\RabbitMQ\Producer\ProducerFactory;

final class Client
{

	/**
	 * @var ProducerFactory
	 */
	private $producerFactory;


	public function __construct(ProducerFactory $producerFactory)
	{
		$this->producerFactory = $producerFactory;
	}


	public function getProducer(string $name): Producer
	{
		try {
			return $this->producerFactory->getProducer($name);

		} catch (Exception $e) {
			
		}
	}

}
