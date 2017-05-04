<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Consumer;

use Gamee\RabbitMQ\Consumer\Exception\ConsumerFactoryException;

final class ConsumerFactory
{

	/**
	 * @var ConsumersDataBag
	 */
	private $ConsumersDataBag;

	/**
	 * @var ConsumerFactory
	 */
	private $ConsumerFactory;

	/**
	 * @var Consumer[]
	 */
	private $Consumers = [];


	public function __construct(
		ConsumersDataBag $ConsumersDataBag
	) {
		$this->ConsumersDataBag = $ConsumersDataBag;
	}


	/**
	 * @throws ConsumerFactoryException
	 */
	public function getConsumer(string $name): Consumer
	{
		if (!isset($this->Consumers[$name])) {
			$this->Consumers[$name] = $this->create($name);
		}

		return $this->Consumers[$name];
	}


	/**
	 * @throws ConsumerFactoryException
	 */
	private function create(string $name): Consumer
	{
		try {
			$ConsumerData = $this->ConsumersDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {

			throw new ConsumerFactoryException("Consumer [$name] does not exist");
		}

		return new Consumer(
			$ConsumerData['host'],
			$ConsumerData['port'],
			$ConsumerData['user'],
			$ConsumerData['password'],
			$ConsumerData['vhost']
		);
	}

}
