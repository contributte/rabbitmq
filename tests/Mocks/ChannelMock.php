<?php

declare(strict_types=1);

namespace Gamee\RabbitMQ\Tests\Mocks;

use Bunny\Channel;
use Gamee\RabbitMQ\Tests\Mocks\Helper\RabbitMQMessageHelper;
use Nette\Neon\Neon;

final class ChannelMock extends Channel
{

	/**
	 * @var RabbitMQMessageHelper
	 */
	private $messageHelper;


	public function __construct()
	{
		$config = Neon::decode(file_get_contents(__DIR__ . '/../config/config.test.neon'));

		$this->messageHelper = RabbitMQMessageHelper::getInstance($config['rabbitmq']);
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
		if ($exchange === '') {
			$this->messageHelper->publishToQueueDirectly($routingKey, $body, $headers);
		} else {
			$this->messageHelper->publishToExchange($exchange, $body, $headers, $routingKey);
		}
	}

}
