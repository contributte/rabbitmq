<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

use Bunny\Client as BunnyClient;
use Bunny\Exception\BunnyException;
use Bunny\Protocol\HeartbeatFrame;

class Client extends BunnyClient
{
	/**
	 * @throws BunnyException
	 */
	public function sendHeartbeat(): void {
		$this->getWriter()->appendFrame(new HeartbeatFrame(), $this->writeBuffer);
		$this->flushWriteBuffer();
	}
}
