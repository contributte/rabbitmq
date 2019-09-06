<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Consumer;

use Bunny\Message;

interface IConsumer
{

	public const MESSAGE_ACK = 1;
	public const MESSAGE_NACK = 2;
	public const MESSAGE_REJECT = 3;
	public const MESSAGE_REJECT_AND_TERMINATE = 4;

	public function consume(Message $message): int;

}
