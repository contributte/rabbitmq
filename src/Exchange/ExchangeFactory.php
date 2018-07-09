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
use Gamee\RabbitMQ\Queue\Exception\QueueFactoryException;
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
	 * @var IExchange[]
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
	public function getExchange(string $name): IExchange
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
	private function create(string $name): IExchange
	{
		$queueBindings = [];

		try {
			$exchangeData = $this->exchangesDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {

			throw new ExchangeFactoryException("Exchange [$name] does not exist");
		}

		$connection = $this->connectionFactory->getConnection($exchangeData['connection']);

		if ($exchangeData['autoCreate']) {
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
		}

		if (!empty($exchangeData['queueBindings'])) {
			foreach ($exchangeData['queueBindings'] as $queueName => $queueBinding) {
				$queue = $this->queueFactory->getQueue($queueName); // (QueueFactoryException)

				if ($exchangeData['autoCreate']) {
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
				}

				$queueBindings[] = new QueueBinding(
					$queue,
					$queueBinding['routingKey'],
					$queueBinding['noWait'],
					$queueBinding['arguments']
				);
			}
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
			$queueBindings,
			$connection
		);
	}

}
