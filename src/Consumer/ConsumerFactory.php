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
	private $consumersDataBag;

	/**
	 * @var Consumer[]
	 */
	private $consumers = [];


	public function __construct(ConsumersDataBag $consumersDataBag)
	{
		$this->consumersDataBag = $consumersDataBag;
	}


	/**
	 * @throws ConsumerFactoryException
	 */
	public function getConsumer(string $name): Consumer
	{
		if (!isset($this->consumers[$name])) {
			$this->consumers[$name] = $this->create($name);
		}

		return $this->consumers[$name];
	}


	/**
	 * @throws ConsumerFactoryException
	 */
	private function create(string $name): Consumer
	{
		try {
			$consumerData = $this->consumersDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {

			throw new ConsumerFactoryException("Consumer [$name] does not exist");
		}

		throw new \RuntimeException('Consumers are not implemented yet');
	}

}
