<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Consumer;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Contributte\RabbitMQ\Queue\IQueue;

class Consumer
{

	protected string $name;

	protected IQueue $queue;

	/** @var callable */
	protected $callback;

	protected int $messages = 0;

	protected ?int $prefetchSize = null;

	protected ?int $prefetchCount = null;

	protected ?int $maxMessages = null;

	public function __construct(
		string $name,
		IQueue $queue,
		callable $callback,
		?int $prefetchSize,
		?int $prefetchCount
	)
	{
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
		$this->maxMessages = $maxMessages;
		$channel = $this->queue->getConnection()->getChannel();

		if ($this->prefetchSize !== null || $this->prefetchCount !== null) {
			$channel->qos($this->prefetchSize ?? 0, $this->prefetchCount ?? 0);
		}

		$channel->consume(
			function (Message $message, Channel $channel, Client $client): void {
				$this->messages++;
				$result = call_user_func($this->callback, $message);

				$this->sendResponse($message, $channel, $result, $client);

				if ($this->isMaxMessages()) {
					$client->stop();
				}
			},
			$this->queue->getName()
		);

		$channel->getClient()->run($maxSeconds);
	}

	protected function sendResponse(Message $message, Channel $channel, int $result, Client $client): void
	{
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

			case IConsumer::MESSAGE_ACK_AND_TERMINATE:
				// Acknowledge message and terminate
				$channel->ack($message);
				$client->stop();

				break;

			default:
				throw new \InvalidArgumentException(
					sprintf('Unknown return value of consumer [%s] user callback', $this->name)
				);
		}
	}

	protected function isMaxMessages(): bool
	{
		return $this->maxMessages !== null && $this->messages >= $this->maxMessages;
	}

}
