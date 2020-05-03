<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Exchange;

use Contributte\RabbitMQ\Queue\IQueue;

final class QueueBinding
{

	private IQueue $queue;

	private string $routingKey;

	/**
	 * @var bool
	 */
	/*private $noWait;*/

	/**
	 * @var array
	 */
	/*private $arguments;*/


	public function __construct(
		IQueue $queue,
		/*,
		bool $noWait,
		array $arguments*/
		string $routingKey
	) {
		$this->queue = $queue;
		$this->routingKey = $routingKey;
		/*$this->noWait = $noWait;
		$this->arguments = $arguments;*/
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
