<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Producer;

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
	 * @throws \InvalidArgumentException
	 */
	public function create(string $name): Producer
	{
		throw new \InvalidArgumentException;
	}

}
