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
		string $vhost
	) {
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
		$this->vhost = $vhost;

		$this->bunnyClient = new Bunny\Client([
			'host' => $this->host,
			'port' => $this->port,
			'user' => $this->user,
			'password' => $this->password,
			'vhost' => $this->vhost,
		]);
	}


	public function getChannel(): Bunny\Channel
	{
		if (!$this->bunnyClient->isConnected()) {
			$this->bunnyClient->connect();
		}
		return $this->bunnyClient->channel();
	}


	public function __destruct()
	{
		$this->bunnyClient->disconnect();
	}

}
