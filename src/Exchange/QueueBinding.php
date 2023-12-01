<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Exchange;

use Contributte\RabbitMQ\Queue\IQueue;

final class QueueBinding
{

	private IQueue $queue;

	private string $routingKey;

	/** @var array<string> */
	private array $routingKeys;

	public function __construct(
		IQueue $queue,
		string $routingKey,
		string ...$routingKeys
	)
	{
		$this->queue = $queue;
		$this->routingKey = $routingKey;
		$this->routingKeys = $routingKeys;
	}

	public function getQueue(): IQueue
	{
		return $this->queue;
	}

	public function getRoutingKey(): string
	{
		return $this->routingKey;
	}

	/**
	 * @return array<string>
	 */
	public function getRoutingKeys(): array
	{
		return $this->routingKeys;
	}

}
