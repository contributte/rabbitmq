<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Producer;

interface IProducer
{
	public const DELIVERY_MODE_NON_PERSISTENT = 1;
	public const DELIVERY_MODE_PERSISTENT = 2;

	/**
	 * @param array<string, string|int> $headers
	 */
	public function publish(string $message, array $headers = [], ?string $routingKey = null): void;
	public function addOnPublishCallback(callable $callback): void;
}
