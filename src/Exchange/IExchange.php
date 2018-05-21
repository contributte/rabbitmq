<?php

declare(strict_types=1);

namespace Gamee\RabbitMQ\Exchange;

use Gamee\RabbitMQ\Connection\IConnection;

interface IExchange
{

	public function getName(): string;


	public function getQueueBindings(): array;


	public function getConnection(): IConnection;
}
