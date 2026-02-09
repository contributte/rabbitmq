<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Bunny\Channel;
use Bunny\Client;
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
	 * @param string $body
	 * @param array<string> $headers
	 * @param string $exchange
	 * @param string $routingKey
	 * @param bool $mandatory
	 * @param bool $immediate
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

	/**
	 * @param string $queue
	 * @param string $consumerTag
	 * @param bool $noLocal
	 * @param bool $noAck
	 * @param bool $exclusive
	 * @param bool $nowait
	 * @param array<mixed> $arguments
	 */
	public function consume(callable $callback, $queue = '', $consumerTag = '', $noLocal = false, $noAck = false, $exclusive = false, $nowait = false, $arguments = []): void
	{
		$this->client->setCallback($callback);
	}

	/**
	 * @param bool $multiple
	 */
	public function ack(Message $message, $multiple = false): void
	{
		if (!isset($this->acks[$this->ackPos])) {
			$this->acks[$this->ackPos] = [];
		}

		$this->acks[$this->ackPos][$message->deliveryTag] = $message->content;
	}

	/**
	 * @param bool $multiple
	 * @param bool $requeue
	 */
	public function nack(Message $message, $multiple = false, $requeue = false): void
	{
		if (!isset($this->nacks[$this->nackPos])) {
			$this->nacks[$this->nackPos] = [];
		}

		$this->nacks[$this->nackPos][$message->deliveryTag] = $message->content;
	}

	public function setClient(Client $client): void
	{
		$this->client = $client;
		$this->client->setChannel($this);
	}

}
