<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Producer;

use Gamee\RabbitMQ\Exchange\IExchange;
use Gamee\RabbitMQ\Queue\IQueue;

final class Producer
{

	public const DELIVERY_MODE_NON_PERSISTENT = 1;
	public const DELIVERY_MODE_PERSISTENT = 2;

	/**
	 * @var IExchange|null
	 */
	private $exchange;

	/**
	 * @var IQueue|null
	 */
	private $queue;

	/**
	 * @var string
	 */
	private $contentType;

	/**
	 * @var int
	 */
	private $deliveryMode;


	public function __construct(
		?IExchange $exchange,
		?IQueue $queue,
		string $contentType,
		int $deliveryMode
	) {
		$this->exchange = $exchange;
		$this->queue = $queue;
		$this->contentType = $contentType;
		$this->deliveryMode = $deliveryMode;
	}


	public function publish(string $message, array $headers = [], ?string $routingKey = null): void
	{
		$headers = array_merge($this->getBasicHeaders(), $headers);

		if ($this->queue !== null) {
			$this->publishToQueue($message, $headers);
		}

		if ($this->exchange !== null) {
			$this->publishToExchange($message, $headers, $routingKey ?? '');
		}
	}


	private function getBasicHeaders(): array
	{
		return [
			'content-type' => $this->contentType,
			'delivery-mode' => $this->deliveryMode,
		];
	}


	private function publishToQueue(string $message, array $headers = []): void
	{
		if ($this->queue === null) {
			throw new \UnexpectedValueException('Queue is not defined');
		}

		$this->queue->getConnection()->getChannel()->publish(
			$message,
			$headers,
			'', // Exchange name
			$this->queue->getName() // Routing key, in this case the queue's name
		);
	}


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
