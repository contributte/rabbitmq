<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Producer;

use Contributte\RabbitMQ\Exchange\IExchange;
use Contributte\RabbitMQ\Queue\IQueue;

final class Producer
{

	public const DELIVERY_MODE_NON_PERSISTENT = 1;
	public const DELIVERY_MODE_PERSISTENT = 2;

	private ?IExchange $exchange = null;

	private ?IQueue $queue = null;

	private string $contentType;

	private int $deliveryMode;

	/** @var callable[] */
	private array $publishCallbacks = [];

	public function __construct(
		?IExchange $exchange,
		?IQueue $queue,
		string $contentType,
		int $deliveryMode
	)
	{
		$this->exchange = $exchange;
		$this->queue = $queue;
		$this->contentType = $contentType;
		$this->deliveryMode = $deliveryMode;
	}

	/**
	 * @param array<string, mixed> $headers
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
		if ($this->queue !== null) {
			$this->queue->getConnection()->sendHeartbeat();
		}

		if ($this->exchange !== null) {
			$this->exchange->getConnection()->sendHeartbeat();
		}
	}

	/**
	 * @return array<string, int|string>
	 */
	private function getBasicHeaders(): array
	{
		return [
			'content-type' => $this->contentType,
			'delivery-mode' => $this->deliveryMode,
		];
	}

	/**
	 * @param array<string, mixed> $headers
	 */
	private function publishToQueue(string $message, array $headers = []): void
	{
		if ($this->queue === null) {
			throw new \UnexpectedValueException('Queue is not defined');
		}

		$this->queue->getConnection()->getChannel()->publish(
			$message,
			$headers,
			// Exchange name
			'',
			// Routing key, in this case the queue's name
			$this->queue->getName()
		);
	}

	/**
	 * @param array<string, mixed> $headers
	 */
	private function publishToExchange(string $message, array $headers, string $routingKey): void
	{
		if ($this->exchange === null) {
			throw new \UnexpectedValueException('Exchange is not defined');
		}

		$this->exchange->getConnection()->getChannel()->publish(
			$message,
			$headers,
			$this->exchange->getName(),
			$routingKey
		);
	}

}
