<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Exception\ClientException;
use Contributte\RabbitMQ\Connection\Exception\ConnectionException;

final class Connection implements IConnection
{

	private Client $bunnyClient;

	/**
	 * @var array
	 */
	private array $connectionParams;
	private ?Channel $channel = null;


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
		bool $lazy = false
	) {
		$this->connectionParams = [
			'host' => $host,
			'port' => $port,
			'user' => $user,
			'password' => $password,
			'vhost' => $vhost,
			'heartbeat' => $heartbeat,
			'timeout' => $timeout,
			'persistent' => $persistent,
			'path' => $path,
			'tcp_nodelay' => $tcpNoDelay,
		];

		$this->bunnyClient = $this->createNewConnection();

		if (!$lazy) {
			$this->bunnyClient->connect();
		}
	}


	public function __destruct()
	{
		$this->bunnyClient->disconnect();
	}


	/**
	 * @throws ConnectionException
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
			if ($e->getMessage() !== 'Broken pipe or closed connection.') {
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

	public function connectIfNeeded(): void
	{
		if ($this->bunnyClient->isConnected()) {
			return;
		}

		$this->bunnyClient->connect();
	}


	private function createNewConnection(): Client
	{
		return new Client($this->connectionParams);
	}
}
