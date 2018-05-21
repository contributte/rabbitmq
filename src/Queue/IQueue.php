<?php

declare(strict_types=1);

namespace Gamee\RabbitMQ\Queue;

use Gamee\RabbitMQ\Connection\IConnection;

interface IQueue
{

	public function getName(): string;


	public function getConnection(): IConnection;
}
