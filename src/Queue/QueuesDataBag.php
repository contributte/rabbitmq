<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Queue;

use Contributte\RabbitMQ\AbstractDataBag;

final class QueuesDataBag extends AbstractDataBag
{

	/**
	 * @param array<mixed> $config
	 */
	public function addQueueByData(string $queueName, array $config): void
	{
		$this->data[$queueName] = $config;
	}

}
