<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

use Bunny\Channel;
use Contributte\RabbitMQ\Connection\Exception\ConnectionException;

interface IConnection
{

	/**
	 * @throws ConnectionException
	 */
	public function getChannel(): Channel;
}
