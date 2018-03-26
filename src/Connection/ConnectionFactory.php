<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Connection;

use Gamee\RabbitMQ\Connection\Exception\ConnectionFactoryException;

final class ConnectionFactory
{

	/**
	 * @var ConnectionsDataBag
	 */
	private $connectionsDataBag;

	/**
	 * @var ConnectionFactory
	 */
	private $connectionFactory;

	/**
	 * @var Connection[]
	 */
	private $connections = [];


	public function __construct(ConnectionsDataBag $connectionsDataBag)
	{
		$this->connectionsDataBag = $connectionsDataBag;
	}


	/**
	 * @throws ConnectionFactoryException
	 */
	public function getConnection(string $name): Connection
	{
		if (!isset($this->connections[$name])) {
			$this->connections[$name] = $this->create($name);
		}

		return $this->connections[$name];
	}


	/**
	 * @throws ConnectionFactoryException
	 */
	private function create(string $name): Connection
	{
		try {
			$connectionData = $this->connectionsDataBag->getDataBykey($name);

		} catch (\InvalidArgumentException $e) {

			throw new ConnectionFactoryException("Connection [$name] does not exist");
		}

		return new Connection(
			$connectionData['host'],
			(int) $connectionData['port'],
			$connectionData['user'],
			$connectionData['password'],
			$connectionData['vhost'],
			(float) $connectionData['heartbeat'],
			(float) $connectionData['timeout']
		);
	}

}
