<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Connection;

use Contributte\RabbitMQ\Connection\Exception\ConnectionFactoryException;

final class ConnectionFactory
{

	private ConnectionsDataBag $connectionsDataBag;

	/** @var IConnection[] */
	private array $connections = [];

	public function __construct(ConnectionsDataBag $connectionsDataBag)
	{
		$this->connectionsDataBag = $connectionsDataBag;
	}

	/**
	 * @throws ConnectionFactoryException
	 */
	public function getConnection(string $name): IConnection
	{
		if (!isset($this->connections[$name])) {
			$this->connections[$name] = $this->create($name);
		}

		return $this->connections[$name];
	}

	/**
	 * @throws ConnectionFactoryException
	 */
	private function create(string $name): IConnection
	{
		try {
			$connectionData = $this->connectionsDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {
			throw new ConnectionFactoryException(sprintf('Connection [%s] does not exist', $name));
		}

		return new Connection(
			$connectionData['host'],
			(int) $connectionData['port'],
			$connectionData['user'],
			$connectionData['password'],
			$connectionData['vhost'],
			(float) $connectionData['heartbeat'],
			(float) $connectionData['timeout'],
			(bool) $connectionData['persistent'],
			$connectionData['path'],
			(bool) $connectionData['tcpNoDelay'],
			(bool) $connectionData['lazy'],
			$connectionData['ssl'],
		);
	}

}
