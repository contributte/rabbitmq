<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Producer;

use Gamee\RabbitMQ\Connection\Connection;
use Gamee\RabbitMQ\Exchange\Exchange;
use Gamee\RabbitMQ\Queue\Queue;

final class Producer
{

	const DELIVERY_MODE_NON_PERSISTENT = 1;
	const DELIVERY_MODE_PERSISTENT = 2;

	/**
	 * @var Exchange|NULL
	 */
	private $exchange;

	/**
	 * @var Queue|NULL
	 */
	private $queue;

	/**
	 * @var string
	 */
	private $contentType;

	/**
	 * @var string
	 */
	private $deliveryMode;


	public function __construct(
		Exchange $exchange = NULL,
		Queue $queue = NULL,
		string $contentType,
		int $deliveryMode
	) {
		$this->exchange = $exchange;
		$this->queue = $queue;
		$this->contentType = $contentType;
		$this->deliveryMode = $deliveryMode;
	}


	public function publish(string $message, array $headers = []): void
	{
		if ($this->queue) {
			$this->publishToQueue($message, $headers);
		}

		if ($this->exchange) {
			$this->publishToExchange($message, $headers);
		}
	}


	private function publishToQueue(string $message, array $headers = []): void
	{
		$this->queue->getConnection()->getChannel()->publish(
			$message,
			$headers,
			'', // Exchange name
			$this->queue->getName() // Routing key, in this case the queue's name
		);
	}


	private function publishToExchange(string $message, array $headers = []): void
	{
		foreach ($this->exchange->getQueueBindings() as $queueBinding) {
			$queueBinding->getQueue()->getConnection()->getChannel()->publish(
				$message,
				$headers,
				$this->exchange->getName(),
				$queueBinding->getRoutingKey()
			);
		}
	}

}
