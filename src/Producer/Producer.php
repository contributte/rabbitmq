<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Producer;

use Bunny\Exception\ClientException;
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
		private ?IExchange     $exchange,
		private ?IQueue        $queue,
		private string         $contentType,
		private int            $deliveryMode,
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


	public function sendHeartbeat(): void
	{
		trigger_error(__METHOD__ . '() is deprecated, use dependency ConnectionFactory::sendHeartbeat().', E_USER_DEPRECATED);
		if ($this->queue !== null) {
			$this->queue->getConnection()->sendHeartbeat();
		}
		if ($this->exchange !== null) {
			$this->exchange->getConnection()->sendHeartbeat();
		}
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

		$this->tryPublish(static fn () => $queue->getConnection()->getChannel()->publish(
			$message,
			$headers,
			'',
			$queue->getName()
		));
	}


	/**
	 * @param array<string, string|int> $headers
	 */
	private function publishToExchange(string $message, array $headers, string $routingKey): void
	{
		if (null === $exchange = $this->exchange) {
			throw new \UnexpectedValueException('Exchange is not defined');
		}

		$this->tryPublish(static fn () => $exchange->getConnection()->getChannel()->publish(
			$message,
			$headers,
			$exchange->getName(),
			$routingKey
		));
	}

	private function tryPublish(callable $publish): void
	{
		try {
			$publish();
		} catch (ClientException $e) {
			if ($e->getCode() === 404) {
				$this->lazyDeclarator->declare();
				$publish();
			}
		}
	}
}
