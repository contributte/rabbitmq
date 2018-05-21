<?php

declare(strict_types=1);

namespace Gamee\RabbitMQ\Connection;

use Bunny\Channel;
use Bunny\Client;
use Gamee\RabbitMQ\Connection\Exception\ConnectionException;

interface IConnection
{

	public function getBunnyClient(): Client;


	/**
	 * @throws ConnectionException
	 */
	public function getChannel(): Channel;
}
