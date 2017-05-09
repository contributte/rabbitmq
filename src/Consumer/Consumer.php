<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Consumer;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Gamee\RabbitMQ\Queue\Queue;

final class Consumer
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var Queue
	 */
	private $queue;

	/**
	 * @var callble
	 */
	private $callback;


	public function __construct(string $name, Queue $queue, callable $callback)
	{
		$this->name = $name;
		$this->queue = $queue;
		$this->callback = $callback;
	}


	public function getQueue(): Queue
	{
		return $this->queue;
	}


	public function getCallback(): callable
	{
		return $this->callback;
	}


	public function consumeForSpecifiedTime(int $seconds): void
	{
		$bunnyClient = $this->queue->getConnection()->getBunnyClient();

		$bunnyClient->channel()->consume(
			function (Message $message, Channel $channel, Client $client): void {
				$result = call_user_func($this->callback, $message);

				switch ($result) {
					case IConsumer::MESSAGE_ACK:
						$channel->ack($message); // Acknowledge message
						break;

					case IConsumer::MESSAGE_NACK:
						$channel->nack($message); // Message will be requeued
						break;

					case IConsumer::MESSAGE_REJECT:
						$channel->reject($message); // Message will be discarded
						break;
					
					default:
						throw new \InvalidArgumentException(
							"Unknown return value of consumer [{$this->name}] user callback"
						);
				}
			},
			$this->queue->getName()
		);

		$bunnyClient->run($seconds); // Client runs for X seconds and then stops
	}

}
