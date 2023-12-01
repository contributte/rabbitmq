<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Connection;

use Bunny\Client as BunnyClient;
use Bunny\ClientStateEnum;
use Bunny\Exception\BunnyException;
use Bunny\Exception\ClientException;
use Bunny\Protocol\HeartbeatFrame;

class Client extends BunnyClient
{

	/**
	 * @throws BunnyException
	 */
	public function sendHeartbeat(): void
	{
		$this->getWriter()->appendFrame(new HeartbeatFrame(), $this->writeBuffer);
		$this->flushWriteBuffer();
	}

	public function syncDisconnect(): bool
	{
		try {
			if ($this->state !== ClientStateEnum::CONNECTED) {
				return false;
			}

			$this->state = ClientStateEnum::DISCONNECTING;

			foreach ($this->channels as $channel) {
				$channelId = $channel->getChannelId();

				$this->channelClose($channelId, 0, '', 0, 0);
				$this->removeChannel($channelId);
			}

			$this->connectionClose(0, '', 0, 0);
			$this->closeStream();
		} catch (ClientException $e) {
			// swallow, we do not care we are not connected, we want to close connection anyway
		}

		$this->init();

		return true;
	}

}
