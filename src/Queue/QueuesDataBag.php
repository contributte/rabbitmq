<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Queue;

use Gamee\RabbitMQ\AbstractDataBag;

final class QueuesDataBag extends AbstractDataBag
{

	public function addQueueByData(string $queueName, array $config): void
	{
		$this->data[$queueName] = $config;
	}
}
