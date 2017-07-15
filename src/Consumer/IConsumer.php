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

	const MESSAGE_ACK = 1;
	const MESSAGE_NACK = 2;
	const MESSAGE_REJECT = 3;

	public function consume(Message $message): int;

}
