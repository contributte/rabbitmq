<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Queue;

use Contributte\RabbitMQ\Connection\IConnection;

final class Queue implements IQueue
{

	private string $name;

	private IConnection $connection;

	public function __construct(
		string $name,
		IConnection $connection
	)
	{
		$this->name = $name;
		$this->connection = $connection;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getConnection(): IConnection
	{
		return $this->connection;
	}

}
