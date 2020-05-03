<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Exchange;

use Contributte\RabbitMQ\Connection\IConnection;

interface IExchange
{

	public function getName(): string;


	public function getQueueBindings(): array;


	public function getConnection(): IConnection;
}
