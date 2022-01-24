<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

interface IApi
{
	public function getFederations(): array;

	public function createFederation(
		string $exchange,
		string $vhost,
		string $uri,
		int $prefetch,
		int $reconnectDelay,
		int $messageTTL,
		int $expires,
		string $ackMode,
		array $policy
	): bool;
}
