<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ;

use Gamee\RabbitMQ\Connection\ConnectionFactory;
use Gamee\RabbitMQ\DI\DataBag\ProducersDataBag;
use Gamee\RabbitMQ\Producer\Producer;
use Gamee\RabbitMQ\Producer\ProducerFactory;

final class Client
{

	/**
	 * @var Producer[]
	 */
	private $producters = [];

	/**
	 * @var ProducerFactory
	 */
	private $producerFactory;

	/**
	 * @var ConnectionFactory
	 */
	private $connectionFactory;

	/**
	 * @var ConsumerFactory
	 */
	private $consumerFactory;


	public function __construct(
		ProducerFactory $producerFactory,
		ConnectionFactory $connectionFactory//,
		//ConsumerFactory $consumerFactory
	) {
		$this->producerFactory = $producerFactory;
		$this->connectionFactory = $connectionFactory;
		//$this->consumerFactory = $consumerFactory;
	}


	public function getProducer(string $name): Producer
	{
		try {
			return $this->producerFactory->getProducer($name);

		} catch (Exception $e) {
			
		}
	}

}
