<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Exchange;

use Gamee\RabbitMQ\Connection\ConnectionFactory;
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
	 * @var ConnectionFactory
	 */
	private $connectionFactory;

	/**
	 * @var Exchange[]
	 */
	private $exchanges;


	public function __construct(
		ExchangesDataBag $exchangesDataBag,
		QueueFactory $queueFactory,
		ConnectionFactory $connectionFactory
	) {
		$this->exchangesDataBag = $exchangesDataBag;
		$this->queueFactory = $queueFactory;
		$this->connectionFactory = $connectionFactory;
	}


	/**
	 * @throws ExchangeFactoryException
	 */
	public function getExchange(string $name): Exchange
	{
		if (!isset($this->exchanges[$name])) {
			$this->exchanges[$name] = $this->create($name);
		}

		return $this->exchanges[$name];
	}


	/**
	 * @throws ExchangeFactoryException
	 * @throws QueueFactoryException
	 */
	private function create(string $name): Exchange
	{
		$queueBindings = [];

		try {
			$exchangeData = $this->exchangesDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {

			throw new ExchangeFactoryException("Exchange [$name] does not exist");
		}

		if (!empty($exchangeData['queueBindings'])) {
			foreach ($exchangeData['queueBindings'] as $queueName => $queueBinding) {
				$queue = $this->queueFactory->getQueue($queueName); // (QueueFactoryException)
				$connection = $queue->getConnection();

				/**
				 * Declare the actual queue
				 */
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

				/**
				 * Create binding to the queue
				 */
				$connection->getChannel()->queueBind(
					$queue->getName(),
					$name,
					$queueBinding['routingKey'],
					$queueBinding['noWait'],
					$queueBinding['arguments']
				);

				$queueBindings[] = new QueueBinding(
					$queue,
					$queueBinding['routingKey'],
					$queueBinding['noWait'],
					$queueBinding['arguments']
				);
			}
		} else {
			/**
			 * @todo Throw an exception or not?
			 */
			throw new ExchangeFactoryException(
				"Exchange [$name] could not be created, it is not bound to any queue"
			);
		}

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
