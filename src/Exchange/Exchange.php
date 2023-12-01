<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Exchange;

use Contributte\RabbitMQ\Connection\IConnection;

final class Exchange implements IExchange
{

	private string $name;

	/** @var array<QueueBinding> */
	private array $queueBindings;

	private IConnection $connection;

	/**
	 * @param array<QueueBinding> $queueBindings
	 */
	public function __construct(
		string $name,
		array $queueBindings,
		IConnection $connection
	)
	{
		$this->name = $name;
		$this->queueBindings = $queueBindings;
		$this->connection = $connection;
	}

	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return array<QueueBinding>
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
