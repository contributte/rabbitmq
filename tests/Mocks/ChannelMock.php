<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Tests\Mocks;

use Bunny\Channel;
use Bunny\Exception\ClientException;
use Bunny\Message;
use Contributte\RabbitMQ\Tests\Mocks\Helper\RabbitMQMessageHelper;
use Nette\Neon\Neon;

final class ChannelMock extends Channel
{

	/**
	 * @var RabbitMQMessageHelper
	 */
	private $messageHelper;
	private bool $withSocketTimeout;

	public $callback;

	public array $acks = [];
	public int $ackPos = 0;

	public array $nacks = [];
	public int $nackPos = 0;

	public function __construct(bool $withSocketTimeout = false)
	{
		$config = Neon::decode(file_get_contents(__DIR__ . '/../config/config.test.neon'));

		$this->messageHelper = RabbitMQMessageHelper::getInstance($config['rabbitmq']);
		$this->withSocketTimeout = $withSocketTimeout;
	}


	public function publish(
		$body,
		array $headers = [],
		$exchange = '',
		$routingKey = '',
		$mandatory = false,
		$immediate = false
	)
	{
		if($this->withSocketTimeout) {
			$this->withSocketTimeout = false;
			throw new ClientException('Could not write data to socket.');
		}

		if ($exchange === '') {
			$this->messageHelper->publishToQueueDirectly($routingKey, $body, $headers);
		} else {
			$this->messageHelper->publishToExchange($exchange, $body, $headers, $routingKey);
		}
	}

	public function consume(callable $callback, $queue = "", $consumerTag = "", $noLocal = false, $noAck = false, $exclusive = false, $nowait = false, $arguments = [])
	{
		$this->client->setCallback($callback);
	}

	public function ack(Message $message, $multiple = false){
		if(!isset($this->acks[$this->ackPos])){
			$this->acks[$this->ackPos] = [];
		}
		$this->acks[$this->ackPos][$message->deliveryTag] = $message->content;
	}

	public function nack(Message $message, $multiple = false, $requeue = false){
		if(!isset($this->nacks[$this->nackPos])){
			$this->nacks[$this->nackPos] = [];
		}
		$this->nacks[$this->nackPos][$message->deliveryTag] = $message->content;
	}

	public function setClient($client) {
		$this->client = $client;
		$this->client->setChannel($this);
	}
}
