<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Queue;

use Contributte\RabbitMQ\Connection\IConnection;

final class Queue implements IQueue
{

	public function __construct(private string $name, private IConnection $connection)
	{
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
