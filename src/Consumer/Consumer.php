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
use Gamee\RabbitMQ\Queue\IQueue;
use Gamee\RabbitMQ\Queue\Queue;

final class Consumer
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var IQueue
	 */
	private $queue;

	/**
	 * @var callable
	 */
	private $callback;

	/**
	 * @var int
	 */
	private $messages = 0;

	/**
	 * @var int|null
	 */
	private $prefetchSize;

	/**
	 * @var int|null
	 */
	private $prefetchCount;


	public function __construct(
		string $name,
		IQueue $queue,
		callable $callback,
		?int $prefetchSize,
		?int $prefetchCount
	) {
		$this->name = $name;
		$this->queue = $queue;
		$this->callback = $callback;
		$this->prefetchSize = $prefetchSize;
		$this->prefetchCount = $prefetchCount;
	}


	public function getQueue(): IQueue
	{
		return $this->queue;
	}


	public function getCallback(): callable
	{
		return $this->callback;
	}

	public function consume(?int $maxSeconds = null, ?int $maxMessages = null): void
	{
		$bunnyClient = $this->queue->getConnection()->getBunnyClient();

		$channel = $bunnyClient->channel();

		if ($this->prefetchSize !== null || $this->prefetchCount !== null) {
			$channel->qos($this->prefetchSize, $this->prefetchCount);
		}

		$channel->consume(
			function (Message $message, Channel $channel, Client $client) use ($maxMessages): void {
				$result = call_user_func($this->callback, $message);

				switch ($result) {
					case IConsumer::MESSAGE_ACK:
						$channel->ack($message); // Acknowledge message
						break;

					case IConsumer::MESSAGE_NACK:
						$channel->nack($message); // Message will be requeued
						break;

					case IConsumer::MESSAGE_REJECT:
						$channel->reject($message, false); // Message will be discarded
						break;

					case IConsumer::MESSAGE_REJECT_AND_TERMINATE:
						$channel->reject($message, false); // Message will be discarded
						$client->stop();
						break;

					default:
						throw new \InvalidArgumentException(
							"Unknown return value of consumer [{$this->name}] user callback"
						);
				}

				if ($maxMessages !== null && ++$this->messages >= $maxMessages) {
					$client->stop();
				}
			},
			$this->queue->getName()
		);

		$bunnyClient->run($maxSeconds);
	}
}
