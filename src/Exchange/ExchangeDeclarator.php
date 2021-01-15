<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Exchange;

use Contributte\RabbitMQ\Connection\ConnectionFactory;
use Contributte\RabbitMQ\Exchange\Exception\ExchangeFactoryException;
use Contributte\RabbitMQ\Queue\QueueFactory;

final class ExchangeDeclarator
{

	private ConnectionFactory $connectionFactory;
	private ExchangesDataBag $exchangesDataBag;
	private QueueFactory $queueFactory;


	public function __construct(
		ConnectionFactory $connectionFactory,
		ExchangesDataBag $exchangesDataBag,
		QueueFactory $queueFactory
	)
	{
		$this->connectionFactory = $connectionFactory;
		$this->exchangesDataBag = $exchangesDataBag;
		$this->queueFactory = $queueFactory;
	}


	public function declareExchange(string $name): void
	{
		try {
			$exchangeData = $this->exchangesDataBag->getDataBykey($name);
		} catch (\InvalidArgumentException $e) {
			throw new ExchangeFactoryException("Exchange [$name] does not exist");
		}

		$connection = $this->connectionFactory->getConnection($exchangeData['connection']);

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

		if ($exchangeData['queueBindings'] !== []) {
			foreach ($exchangeData['queueBindings'] as $queueName => $queueBinding) {
				$queue = $this->queueFactory->getQueue($queueName);

				$connection->getChannel()->queueBind(
					$queue->getName(),
					$name,
					$queueBinding['routingKey'],
					$queueBinding['noWait'],
					$queueBinding['arguments']
				);
			}
		}
	}
}
