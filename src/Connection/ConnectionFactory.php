<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

use Contributte\RabbitMQ\Connection\Exception\ConnectionFactoryException;

final class ConnectionFactory
{

	private ConnectionsDataBag $connectionsDataBag;

	/**
	 * @var IConnection[]
	 */
	private array $connections = [];

	/**
	 * @var IApi[]
	 */
	private array $requests = [];


	public function __construct(ConnectionsDataBag $connectionsDataBag)
	{
		$this->connectionsDataBag = $connectionsDataBag;
	}


	/**
	 * @return IConnection[]
	 */
	public function getConnections(): array
	{
		return $this->connections;
	}


	/**
	 * @throws ConnectionFactoryException|\Exception
	 */
	public function getConnection(string $name): IConnection
	{
		if (!isset($this->connections[$name])) {
			$this->connections[$name] = $this->create($name);
		}

		return $this->connections[$name];
	}


	public function getApi(string $name): IApi
	{
		if (!isset($this->requests[$name])) {
			$this->requests[$name] = $this->createApi($name);
		}

		return $this->requests[$name];
	}

	/**
	 * @return IConnection[]
	 */
	public function getConnections(): array
	{
		return $this->connections;
	}


	/**
	 * @throws ConnectionFactoryException
	 */
	private function createApi(string $name): Api
	{
		try {
			if (!extension_loaded('curl')) {
				throw new \RuntimeException('RabbitMQ API requires cURL extension.');
			}

			$connectionData = $this->connectionsDataBag->getDataBykey($name);

			if (!isset($connectionData['admin']['port'])) {
				throw new ConnectionFactoryException("Connection [$name] does not have admin port");
			}
		} catch (\InvalidArgumentException $e) {
			throw new ConnectionFactoryException("Connection [$name] does not exist");
		}

		return new Api(
			$connectionData['user'],
			$connectionData['password'],
			$connectionData['admin']['secure'] ?? false,
			$connectionData['host'],
			$connectionData['admin']['port']
		);
	}


	/**
	 * @throws ConnectionFactoryException|\Exception
	 */
	private function create(string $name): IConnection
	{
		try {
			$connectionData = $this->connectionsDataBag->getDataBykey($name);
		} catch (\InvalidArgumentException $e) {
			throw new ConnectionFactoryException("Connection [$name] does not exist");
		}

		return new Connection(
			$connectionData['host'],
			$connectionData['port'],
			$connectionData['user'],
			$connectionData['password'],
			$connectionData['vhost'],
			$connectionData['heartbeat'],
			$connectionData['timeout'],
			$connectionData['persistent'],
			$connectionData['path'],
			$connectionData['tcpNoDelay'],
			$connectionData['lazy'],
			$connectionData['ssl']
		);
	}

}
