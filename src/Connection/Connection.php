<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Connection;

use Bunny;

final class Connection
{

	/**
	 * @var Bunny\Client
	 */
	private $bunnyClient;


	public function __construct(
		string $host,
		int $port,
		string $user,
		string $password,
		string $vhost,
		float $heartbeat,
		float $timeout
	) {
		$this->bunnyClient = new Bunny\Client([
			'host' => $host,
			'port' => $port,
			'user' => $user,
			'password' => $password,
			'vhost' => $vhost,
			'heartbeat' => $heartbeat,
			'timeout' => $timeout,
		]);

		$this->bunnyClient->connect();
	}


	public function getBunnyClient(): Bunny\Client
	{
		return $this->bunnyClient;
	}


	public function getChannel(): Bunny\Channel
	{
		return $this->bunnyClient->channel();
	}


	public function __destruct()
	{
		$this->bunnyClient->disconnect();
	}

}
