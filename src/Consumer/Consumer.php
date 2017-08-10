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
	 * @var callable
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
				$this->handleMessage($message, $channel, $client);
			},
			$this->queue->getName()
		);

		$bunnyClient->run($seconds); // Client runs for X seconds and then stops
	}


	public function consumeSpecifiedAmountOfMessages(int $amountOfMessages): void
	{
		$bunnyClient = $this->queue->getConnection()->getBunnyClient();

		$consumedMessages = 0;
		$bunnyClient->channel()->consume(
			function (Message $message, Channel $channel, Client $client) use (&$consumedMessages, $amountOfMessages): void {
				$this->handleMessage($message, $channel, $client);

				if (++$consumedMessages >= $amountOfMessages) {
					$client->stop();
				}
			},
			$this->queue->getName()
		);

		$bunnyClient->run();
	}


	private function handleMessage(Message $message, Channel $channel, Client $client): void
	{
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

			default:
				throw new \InvalidArgumentException(
					"Unknown return value of consumer [{$this->name}] user callback"
				);
		}
	}
}
