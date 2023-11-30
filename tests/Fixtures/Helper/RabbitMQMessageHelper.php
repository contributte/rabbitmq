<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Tests\Fixtures\Helper;

final class RabbitMQMessageHelper
{

	/**
	 * @var RabbitMQMessageHelper
	 */
	private static $self;

	/**
	 * @var string[][]
	 */
	private $queueMessages = [];

	/**
	 * @var array
	 */
	private $config;


	private function __construct(array $config)
	{
		self::$self = $this;
		$this->config = $config;
	}


	public static function getInstance(array $config = null): self
	{
		if (self::$self === null) {
			if ($config === null) {
				throw new \Exception('Missing config');
			}
			self::$self = new RabbitMQMessageHelper($config);
		}

		return self::$self;
	}


	public function reinit(): void
	{
		$this->queueMessages = [];
	}

	public function publishToQueueDirectly(
		string $queueName,
		string $body,
		array $headers = []
	): void
	{
		$this->queueMessages[$queueName][] = [
			'body' => $body,
			'headers' => $headers,
		];
	}


	public function publishToExchange(
		string $exchangeName,
		string $body,
		array $headers,
		string $routingKey
	): void
	{
		$exchangeConfig = $this->config['exchanges'][$exchangeName];
		switch ($exchangeConfig['type']) {
			case 'fanout':
				$this->publishFanoutMessage($exchangeConfig, $body, $headers);
				break;

			case 'direct':
				$this->publishDirectMessage($exchangeConfig, $body, $headers, $routingKey);
				break;

			case 'headers':
				$this->publishHeadersMessage($exchangeConfig, $body, $headers);
				break;

			case 'topic':
				$this->publishTopicMessage($exchangeConfig, $body, $headers, $routingKey);
				break;

		}

	}


	public function getQueueMessages(string $queueName = null): array
	{
		if ($queueName === null) {
			$messages = $this->queueMessages;
		} else {
			$messages = $this->queueMessages[$queueName] ?? [];
		}

		return $messages;
	}


	private function publishFanoutMessage(
		array $exchangeConfig,
		string $body,
		array $headers
	): void
	{
		foreach ($exchangeConfig['queueBindings'] as $queueName => $queueBinding) {
			$this->publishToQueueDirectly($queueName, $body, $headers);
		}
	}


	private function publishDirectMessage(
		array $exchangeConfig,
		string $body,
		array $headers,
		string $routingKey
	): void
	{
		foreach ($exchangeConfig['queueBindings'] as $queueName => $queueBinding) {
			if ($queueBinding['routingKey'] === $routingKey) {
				$this->publishToQueueDirectly($queueName, $body, $headers);
			}
		}
	}


	private function publishHeadersMessage(
		array $exchangeConfig,
		string $body,
		array $headers
	)
	{
		throw new \LogicException('Not implemented');
	}


	private function publishTopicMessage(
		array $exchangeConfig,
		string $body,
		array $headers,
		string $routingKey
	)
	{
		throw new \LogicException('Not implemented');
	}

}
