<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Exchange;

use Contributte\RabbitMQ\Queue\IQueue;

final class QueueBinding
{

	public function __construct(private IQueue $queue, private string $routingKey)
	{
	}


	public function getQueue(): IQueue
	{
		return $this->queue;
	}


	public function getRoutingKey(): string
	{
		return $this->routingKey;
	}
}
