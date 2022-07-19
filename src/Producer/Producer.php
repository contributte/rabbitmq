<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Producer;

use Bunny\Exception\ClientException;
use Contributte\RabbitMQ\Connection\Exception\PublishException;
use Contributte\RabbitMQ\Exchange\IExchange;
use Contributte\RabbitMQ\LazyDeclarator;
use Contributte\RabbitMQ\Queue\IQueue;

final class Producer
{

	public const DELIVERY_MODE_NON_PERSISTENT = 1;
	public const DELIVERY_MODE_PERSISTENT = 2;

	/**
	 * @var callable[]
	 */
	private array $publishCallbacks = [];

	public function __construct(
		private ?IExchange $exchange,
		private ?IQueue $queue,
		private string $contentType,
		private int $deliveryMode,
		private LazyDeclarator $lazyDeclarator
	) {
	}

	/**
	 * @param array<string, string|int> $headers
	 */
	public function publish(string $message, array $headers = [], ?string $routingKey = null): void
	{
		$headers = array_merge($this->getBasicHeaders(), $headers);

		if ($this->queue !== null) {
			$this->publishToQueue($message, $headers);
		}

		if ($this->exchange !== null) {
			$this->publishToExchange($message, $headers, $routingKey ?? '');
		}

		foreach ($this->publishCallbacks as $callback) {
			($callback)($message, $headers, $routingKey);
		}
	}


	public function addOnPublishCallback(callable $callback): void
	{
		$this->publishCallbacks[] = $callback;
	}


	/**
	 * @return array<string, string|int>
	 */
	private function getBasicHeaders(): array
	{
		return [
			'content-type' => $this->contentType,
			'delivery-mode' => $this->deliveryMode,
		];
	}

	/**
	 * @param array<string, string|int> $headers
	 */
	private function publishToQueue(string $message, array $headers = []): void
	{
		if (null === $queue = $this->queue) {
			throw new \UnexpectedValueException('Queue is not defined');
		}

		$this->tryPublish($queue, $message, $headers, '', $queue->getName());
	}


	/**
	 * @param array<string, string|int> $headers
	 */
	private function publishToExchange(string $message, array $headers, string $routingKey): void
	{
		if (null === $exchange = $this->exchange) {
			throw new \UnexpectedValueException('Exchange is not defined');
		}

		$this->tryPublish($exchange, $message, $headers, $exchange->getName(), $routingKey);
	}

	/**
	 * @param array<string, string|int> $headers
	 */
	private function tryPublish(IQueue|IExchange $target, string $message, array $headers, string $exchange, string $routingKey, int $try = 0): void
	{
		try {
			$deliveryTag = $target->getConnection()->getChannel()->publish(
				$message,
				$headers,
				$exchange,
				$routingKey
			);
			$confirm = $target->getConnection()->getPublishConfirm();
			if ($confirm !== null && $confirm->deliveryTag() === $deliveryTag && !$confirm->isAck()) {
				throw new PublishException("Publish of message failed.\nExchange:{$exchange}\nRoutingKey:{$routingKey}");
			}
		} catch (ClientException $e) {
			if ($try >= 2) {
				throw $e;
			}

			// Exchange do not exists, lazy declare
			if ($e->getCode() === 404) {
				$this->lazyDeclarator->declare();
				$this->tryPublish($target, $message, $headers, $exchange, $routingKey, ++$try);
				return;
			}

			// Try to reset connection if issue is broken pipe or closed connection
			if (in_array(
				$e->getMessage(),
				['Broken pipe or closed connection.', 'Could not write data to socket.'],
				true
			)) {
				$target->getConnection()->resetChannel();
				$target->getConnection()->resetConnection();

				$this->tryPublish($target, $message, $headers, $exchange, $routingKey, ++$try);
				return;
			}

			throw $e;
		}
	}
}
