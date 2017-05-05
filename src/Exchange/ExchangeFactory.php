<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Exchange;

use Gamee\RabbitMQ\Connection\Connection;
use Gamee\RabbitMQ\Exchange\Exception\ExchangeFactoryException;
use Gamee\RabbitMQ\Queue\QueueFactory;

final class ExchangeFactory
{

	/**
	 * @var ExchangesDataBag
	 */
	private $exchangesDataBag;

	/**
	 * @var QueueFactory
	 */
	private $queueFactory;

	/**
	 * @var Exchange[]
	 */
	private $exchanges;


	public function __construct(ExchangesDataBag $exchangesDataBag, QueueFactory $queueFactory)
	{
		$this->exchangesDataBag = $exchangesDataBag;
		$this->queueFactory = $queueFactory;
	}


	/**
	 * @throws ExchangeFactoryException
	 */
	public function getExchange(string $name, Connection $connection): Exchange
	{
		if (!isset($this->exchanges[$name])) {
			$this->exchanges[$name] = $this->create($name, $connection);
		}

		return $this->exchanges[$name];
	}


	/**
	 * @throws ExchangeFactoryException
	 * @throws QueueFactoryException
	 */
	private function create(string $name, Connection $connection): Exchange
	{
		$queueBindings = [];

		try {
			$exchangeData = $this->exchangesDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {

			throw new ExchangeFactoryException("Exchange [$name] does not exist");
		}

		if (!empty($exchangeData['queueBindings'])) {
			foreach ($exchangeData['queueBindings'] as $queueName => $queueBinding) {
				$queueBindings[] = new QueueBinding(
					$this->queueFactory->getQueue($queueName), // (QueueFactoryException)
					$queueBinding['routingKey'],
					$queueBinding['noWait'],
					$queueBinding['arguments']
				);
			}
		}

		$connection->getChannel()->exchangeDeclare(
			$name,
			$exchangeData['type'],
			$exchangeData['passive'],
			$exchangeData['durable'],
			$exchangeData['autoDelete'],
			$exchangeData['internal'],
			$exchangeData['noWait'],
			$exchangeData['arguments']
		);

		return new Exchange(
			$name,
			$exchangeData['type'],
			$exchangeData['passive'],
			$exchangeData['durable'],
			$exchangeData['autoDelete'],
			$exchangeData['internal'],
			$exchangeData['noWait'],
			$exchangeData['arguments'],
			$queueBindings
		);;
	}

}
