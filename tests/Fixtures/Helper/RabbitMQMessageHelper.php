<?php declare(strict_types = 1);

namespace Tests\Fixtures\Helper;

final class RabbitMQMessageHelper
{

	private static ?RabbitMQMessageHelper $self = null;

	/** @var string[][] */
	private array $queueMessages = [];

	/** @var array<mixed> */
	private array $config;

	/**
	 * @param array<mixed> $config
	 */
	private function __construct(array $config)
	{
		self::$self = $this;
		$this->config = $config;
	}

	/**
	 * @param array<mixed>|null $config
	 */
	public static function getInstance(?array $config = null): self
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

	/**
	 * @param array<string> $headers
	 */
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

	/**
	 * @param array<string> $headers
	 */
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

	/**
	 * @return string[][]
	 */
	public function getQueueMessages(?string $queueName = null): array
	{
		return $queueName === null ? $this->queueMessages : $this->queueMessages[$queueName] ?? [];
	}

	/**
	 * @param array<mixed> $exchangeConfig
	 * @param array<string> $headers
	 */
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

	/**
	 * @param array<mixed> $exchangeConfig
	 * @param array<string> $headers
	 */
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

	/**
	 * @param array<mixed> $exchangeConfig
	 * @param array<string> $headers
	 */
	private function publishHeadersMessage(
		array $exchangeConfig,
		string $body,
		array $headers
	): void
	{
		throw new \LogicException('Not implemented');
	}

	/**
	 * @param array<mixed> $exchangeConfig
	 * @param array<string> $headers
	 */
	private function publishTopicMessage(
		array $exchangeConfig,
		string $body,
		array $headers,
		string $routingKey
	): void
	{
		throw new \LogicException('Not implemented');
	}

}
