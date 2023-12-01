<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Bunny\Channel;
use Bunny\Message;
use Nette\Neon\Neon;
use Tests\Fixtures\Helper\RabbitMQMessageHelper;

final class ChannelMock extends Channel
{

	/** @var array<mixed> */
	public array $acks = [];

	public int $ackPos = 0;

	/** @var array<mixed> */
	public array $nacks = [];

	public int $nackPos = 0;

	private RabbitMQMessageHelper $messageHelper;

	public function __construct()
	{
		$config = Neon::decode(file_get_contents(__DIR__ . '/config/config.test.neon'));

		$this->messageHelper = RabbitMQMessageHelper::getInstance($config['rabbitmq']);
	}

	/**
	 * @param array<string> $headers
	 */
	public function publish(
		$body,
		array $headers = [],
		$exchange = '',
		$routingKey = '',
		$mandatory = false,
		$immediate = false
	): void
	{
		if ($exchange === '') {
			$this->messageHelper->publishToQueueDirectly($routingKey, $body, $headers);
		} else {
			$this->messageHelper->publishToExchange($exchange, $body, $headers, $routingKey);
		}
	}

	public function consume(callable $callback, $queue = '', $consumerTag = '', $noLocal = false, $noAck = false, $exclusive = false, $nowait = false, $arguments = []): void
	{
		$this->client->setCallback($callback);
	}

	public function ack(Message $message, $multiple = false): void
	{
		if (!isset($this->acks[$this->ackPos])) {
			$this->acks[$this->ackPos] = [];
		}

		$this->acks[$this->ackPos][$message->deliveryTag] = $message->content;
	}

	public function nack(Message $message, $multiple = false, $requeue = false): void
	{
		if (!isset($this->nacks[$this->nackPos])) {
			$this->nacks[$this->nackPos] = [];
		}

		$this->nacks[$this->nackPos][$message->deliveryTag] = $message->content;
	}

	public function setClient($client): void
	{
		$this->client = $client;
		$this->client->setChannel($this);
	}

}
