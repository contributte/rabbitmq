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

final class Consumer
{

	private string $name;

	private IQueue $queue;

	/**
	 * @var callable
	 */
	private $callback;

	private int $messages = 0;

	private ?int $prefetchSize = null;

	private ?int $prefetchCount = null;


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

		if (!$channel instanceof Channel) {
			throw new \UnexpectedValueException;
		}

		if ($this->prefetchSize !== null || $this->prefetchCount !== null) {
			$channel->qos($this->prefetchSize ?? 0, $this->prefetchCount ?? 0);
		}

		$channel->consume(
			function (Message $message, Channel $channel, Client $client) use ($maxMessages): void {
				$result = call_user_func($this->callback, $message);

				switch ($result) {
					case IConsumer::MESSAGE_ACK:
						// Acknowledge message
						$channel->ack($message);

						break;

					case IConsumer::MESSAGE_NACK:
						// Message will be requeued
						$channel->nack($message);

						break;

					case IConsumer::MESSAGE_REJECT:
						// Message will be discarded
						$channel->reject($message, false);

						break;

					case IConsumer::MESSAGE_REJECT_AND_TERMINATE:
						// Message will be discarded
						$channel->reject($message, false);
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
