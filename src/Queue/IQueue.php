<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Queue;

use Contributte\RabbitMQ\Connection\IConnection;

interface IQueue
{

	public function getName(): string;

	public function getConnection(): IConnection;

}
