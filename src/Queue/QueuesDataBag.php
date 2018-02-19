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

	public function __construct(array $data)
	{
		foreach ($data as $producerName => $producer) {
			$this->addQueueByData($producerName, $producer);
		}
	}


	public function addQueueByData(string $queueName, array $data): void
	{
		$this->data[$queueName] = $data;
	}
}
