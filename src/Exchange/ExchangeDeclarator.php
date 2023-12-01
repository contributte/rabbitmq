<?php declare(strict_types = 1);

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
			throw new ExchangeFactoryException(sprintf('Exchange [%s] does not exist', $name));
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

				$routingKeysToBind = [];

				if (isset($queueBinding['routingKeys'])
					&& $queueBinding['routingKeys'] !== []
				) {
					$routingKeysToBind = $queueBinding['routingKeys'];
				} elseif (isset($queueBinding['routingKey'])) {
					$routingKeysToBind = [$queueBinding['routingKey']];
				}

				foreach ($routingKeysToBind as $routingKey) {
					$connection->getChannel()->queueBind(
						$queue->getName(),
						$name,
						$routingKey,
						$queueBinding['noWait'],
						$queueBinding['arguments']
					);
				}
			}
		}
	}

}
