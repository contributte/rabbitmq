<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

use Bunny\Channel;
use Bunny\Exception\ClientException;
use Contributte\RabbitMQ\Connection\Exception\ConnectionException;

final class Connection implements IConnection
{

	private const HEARTBEAT_INTERVAL = 1;

	private Client $bunnyClient;
	private array $connectionParams;
	private int $lastBeat = 0;
	private ?Channel $channel = null;
	private array $onConnect = [];


	/**
	 * @throws \Exception
	 */
	public function __construct(
		string $host,
		int $port,
		string $user,
		string $password,
		string $vhost,
		float $heartbeat,
		float $timeout,
		bool $persistent,
		string $path,
		bool $tcpNoDelay,
		bool $lazy = false,
		?array $ssl = null
	) {
		$this->connectionParams = [
			'host' => $host,
			'port' => $port,
			'user' => $user,
			'password' => $password,
			'vhost' => $vhost,
			'heartbeat' => $heartbeat,
			'timeout' => $timeout,
			'read_write_timeout' => $timeout,
			'persistent' => $persistent,
			'path' => $path,
			'tcp_nodelay' => $tcpNoDelay,
			'ssl' => $ssl,
		];

		$this->bunnyClient = $this->createNewConnection();

		if (!$lazy) {
			$this->bunnyClient->connect();
		}
	}


	public function __destruct()
	{
		if ($this->bunnyClient->isConnected()) {
			$this->bunnyClient->syncDisconnect();
		}
	}

	public function onConnect(callable $callback): void
	{
		if ($this->bunnyClient->isConnected()) {
			$callback();

			return;
		}

		$this->onConnect[] = $callback;
	}


	/**
	 * @throws ConnectionException|\Exception
	 */
	public function getChannel(): Channel
	{
		if ($this->channel instanceof Channel) {
			return $this->channel;
		}

		try {
			$this->connectIfNeeded();
			$channel = $this->bunnyClient->channel();

			if (!$channel instanceof Channel) {
				throw new \UnexpectedValueException;
			}

			$this->channel = $channel;
		} catch (ClientException $e) {
			if (!in_array(
				$e->getMessage(),
				['Broken pipe or closed connection.', 'Could not write data to socket.'],
				true
			)) {
				throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
			}

			/**
			 * Try to reconnect
			 */
			$this->bunnyClient = $this->createNewConnection();

			$channel = $this->bunnyClient->channel();

			if (!$channel instanceof Channel) {
				throw new \UnexpectedValueException;
			}

			$this->channel = $channel;
		}

		return $this->channel;
	}


	/**
	 * @throws \Exception
	 */
	public function connectIfNeeded(): void
	{
		if ($this->bunnyClient->isConnected()) {
			return;
		}

		$this->bunnyClient->connect();
		$this->invokeCallbacks();
	}


	public function sendHeartbeat(): void
	{
		$now = time();
		if ($this->lastBeat < ($now - self::HEARTBEAT_INTERVAL) && $this->bunnyClient->isConnected()) {
			$this->bunnyClient->sendHeartbeat();
			$this->lastBeat = $now;
		}
	}


	public function getVhost(): string
	{
		return $this->connectionParams['vhost'];
	}

	private function invokeCallbacks(): void
	{
		foreach ($this->onConnect as $callback) {
			$callback();
		}
	}

	private function createNewConnection(): Client
	{
		return new Client($this->connectionParams);
	}
}
