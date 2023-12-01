<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Consumer;

use Bunny\Message;

interface IConsumer
{

	public const MESSAGE_ACK = 1;
	public const MESSAGE_NACK = 2;
	public const MESSAGE_REJECT = 3;
	public const MESSAGE_REJECT_AND_TERMINATE = 4;
	public const MESSAGE_ACK_AND_TERMINATE = 5;

	public function consume(Message $message): int;

}
