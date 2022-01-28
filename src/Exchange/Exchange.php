<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Exchange;

use Contributte\RabbitMQ\Connection\IConnection;

final class Exchange implements IExchange
{

	/**
	 * @param QueueBinding[] $queueBindings
	 */
	public function __construct(
		private string $name,
		private array $queueBindings,
		private IConnection $connection
	) {
	}


	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return QueueBinding[]
	 */
	public function getQueueBindings(): array
	{
		return $this->queueBindings;
	}


	public function getConnection(): IConnection
	{
		return $this->connection;
	}
}
