<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Connection;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Exception\ClientException;
use Gamee\RabbitMQ\Connection\Exception\ConnectionException;

final class Connection implements IConnection
{

	/**
	 * @var Client
	 */
	private $bunnyClient;

	/**
	 * @var array
	 */
	private $connectionParams;

	/**
	 * @var Channel|null
	 */
	private $channel;


	public function __construct(
		string $host,
		int $port,
		string $user,
		string $password,
		string $vhost,
		float $heartbeat,
		float $timeout
	) {
		$this->connectionParams = [
			'host' => $host,
			'port' => $port,
			'user' => $user,
			'password' => $password,
			'vhost' => $vhost,
			'heartbeat' => $heartbeat,
			'timeout' => $timeout,
		];

		$this->bunnyClient = $this->createNewConnection();

		$this->bunnyClient->connect();
	}


	public function getBunnyClient(): Client
	{
		return $this->bunnyClient;
	}


	/**
	 * @throws ConnectionException
	 */
	public function getChannel(): Channel
	{
		if (!$this->channel instanceof Channel) {
			try {
				$this->channel = $this->bunnyClient->channel();
			} catch (ClientException $e) {
				if ($e->getMessage() !== 'Broken pipe or closed connection.') {
					throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
				}

				/**
				 * Try to reconnect
				 */
				$this->bunnyClient = $this->createNewConnection();

				$this->channel = $this->bunnyClient->channel();
			}
		}

		return $this->channel;
	}


	public function __destruct()
	{
		$this->bunnyClient->disconnect();
	}


	private function createNewConnection(): Client
	{
		return new Client($this->connectionParams);
	}
}
