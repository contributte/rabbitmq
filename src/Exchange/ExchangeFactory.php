<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Exchange;

use Contributte\RabbitMQ\Connection\ConnectionFactory;
use Contributte\RabbitMQ\Exchange\Exception\ExchangeFactoryException;
use Contributte\RabbitMQ\Queue\Exception\QueueFactoryException;
use Contributte\RabbitMQ\Queue\QueueFactory;

final class ExchangeFactory
{

	private ExchangesDataBag $exchangesDataBag;
	private QueueFactory $queueFactory;
	private ConnectionFactory $connectionFactory;

	/**
	 * @var IExchange[]
	 */
	private array $exchanges;
	private ExchangeDeclarator $exchangeDeclarator;


	public function __construct(
		ExchangesDataBag $exchangesDataBag,
		QueueFactory $queueFactory,
		ExchangeDeclarator $exchangeDeclarator,
		ConnectionFactory $connectionFactory
	)
	{
		$this->exchangesDataBag = $exchangesDataBag;
		$this->queueFactory = $queueFactory;
		$this->connectionFactory = $connectionFactory;
		$this->exchangeDeclarator = $exchangeDeclarator;
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
			$this->exchangeDeclarator->declareExchange($name);
		}

		if ($exchangeData['queueBindings'] !== []) {
			foreach ($exchangeData['queueBindings'] as $queueName => $queueBinding) {
				// (QueueFactoryException)
				$queue = $this->queueFactory->getQueue($queueName);

				$queueBindings[] = new QueueBinding(
					$queue,
					$queueBinding['routingKey']
				);
			}
		}

		return new Exchange(
			$name,
			$queueBindings,
			$connection
		);
	}
}
