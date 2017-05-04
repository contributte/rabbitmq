<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ;

use Gamee\RabbitMQ\DI\DataBag\ProducersDataBag;

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


	public function __construct()
	{
		
	}


	public function getProducer(string $name): Producer
	{
		// Code here
	}

}
