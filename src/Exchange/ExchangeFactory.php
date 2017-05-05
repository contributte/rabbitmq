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

final class ExchangeFactory
{

	/**
	 * @var ExchangesDataBag
	 */
	private $exchangesDataBag;

	/**
	 * @var Exchange[]
	 */
	private $exchanges;


	public function __construct(ExchangesDataBag $exchangesDataBag)
	{
		$this->exchangesDataBag = $exchangesDataBag;
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
	 */
	private function create(string $name, Connection $connection): Exchange
	{
		try {
			$exchangeData = $this->exchangesDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {

			throw new ExchangeFactoryException("Exchange [$name] does not exist");
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
			$exchangeData['arguments']
		);;
	}

}
