<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

use Bunny\Channel;
use Bunny\Exception\BunnyException;
use Bunny\Protocol\MethodFrame;
use Contributte\RabbitMQ\Connection\Exception\ConnectionException;

interface IConnection
{

	/**
	 * @throws ConnectionException
	 */
	public function getChannel(): Channel;

	/**
	 * @throws BunnyException
	 */
	public function sendHeartbeat(): void;
	public function isConnected(): bool;
	public function getVhost(): string;
	public function isPublishConfirm(): bool;

	/** @internal */
	public function resetChannel(): void;
	/** @internal */
	public function resetConnection(): void;
}
