<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Queue;

use Contributte\RabbitMQ\AbstractDataBag;

final class QueuesDataBag extends AbstractDataBag
{

	/**
	 * @param string $queueName
	 * @param array<string, mixed> $config
	 * @return void
	 */
	public function addQueueByData(string $queueName, array $config): void
	{
		$this->data[$queueName] = $config;
	}
}
