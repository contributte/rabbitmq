<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Consumer;

use Bunny\Channel;
use Bunny\Message;

class BulkMessage
{

	private Message $message;

	private Channel $channel;

	public function __construct(
		Message $message,
		Channel $channel
	)
	{
		$this->message = $message;
		$this->channel = $channel;
	}

	public function getMessage(): Message
	{
		return $this->message;
	}

	public function getChannel(): Channel
	{
		return $this->channel;
	}

}
