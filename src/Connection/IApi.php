<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

interface IApi
{
	/**
	 * @return array<int, mixed>
	 */
	public function getFederations(): array;

	/**
	 * @param string $exchange
	 * @param string $vhost
	 * @param string $uri
	 * @param int $prefetch
	 * @param int $reconnectDelay
	 * @param int $messageTTL
	 * @param int $expires
	 * @param string $ackMode
	 * @param array<string, mixed> $policy
	 * @return bool
	 */
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
