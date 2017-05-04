<?php

namespace Gamee\RabbitMQ\Tests\Cases;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php'; 

final class ProducerTest extends TestCase
{

	/**
	 * @var Client
	 */
	private $bunny;


	public function setUp()
	{
		$this->bunny = new Client([
			'host' => 'localhost',
			'port' => '5672'
		]);
		$this->bunny->connect();
	}


	public function testPublish()
	{
		$channel = $this->bunny->channel();

		$channel->queueDeclare('hello_durable', false, true, false, false);

		$channel->publish('Hello', [], '', 'hello_durable');

		echo "Sent 'Hello World!'\n";
	}


	public function testConsume()
	{
		$channel = $this->bunny->channel();

		$channel->consume(
			function (Message $message, Channel $channel, Client $client) {
				var_dump($message->content);
				$channel->ack($message);
			},
			'hello_durable'
		);

		$this->bunny->run(1);
	}


	public function tearDown()
	{
		Assert::true(true);

		$this->bunny->disconnect();
	}

}

$testCase = new ProducerTest;
$testCase->run();
