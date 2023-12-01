<?php declare(strict_types = 1);

namespace Tests\Cases;

use Bunny\Client;
use Bunny\Message;
use Contributte\RabbitMQ\Connection\IConnection;
use Contributte\RabbitMQ\Consumer\Consumer;
use Contributte\RabbitMQ\Consumer\IConsumer;
use Contributte\RabbitMQ\Queue\IQueue;
use Tester\Assert;
use Tester\TestCase;
use Tests\Fixtures\ChannelMock;

require __DIR__ . '/../bootstrap.php';

final class ConsumerTest extends TestCase
{

	public function testConsumeMessages(): void
	{
		$client = $this->createClient();

		$channelMock = new ChannelMock();
		$channelMock->setClient($client);

		$connectionMock = \Mockery::mock(IConnection::class)
			->shouldReceive('getChannel')->andReturn($channelMock)->getMock();

		$queueMock = \Mockery::mock(IQueue::class)
			->shouldReceive('getConnection')->andReturn($connectionMock)->getMock()
			->shouldReceive('getName')->andReturn('testQueue')->getMock();

		$countOfConsumerCallbackCalls = 0;
		$callback = function (Message $message) use (&$countOfConsumerCallbackCalls) {
			$countOfConsumerCallbackCalls++;
			Assert::same('{"test":"' . $countOfConsumerCallbackCalls . '"}', $message->content, 'Consume message - content');
			Assert::same((string) $countOfConsumerCallbackCalls, $message->deliveryTag, 'Consume message - deliveryTag');

			return IConsumer::MESSAGE_ACK;
		};

		$instance = new Consumer('bulkTest', $queueMock, $callback, null, null);

		$instance->consume(1);

		Assert::same(2, $countOfConsumerCallbackCalls, 'Number of consumer callback calls');
		Assert::count(1, $channelMock->acks, 'Number of ACKs');
		Assert::same([
			1 => [
				1 => '{"test":"1"}',
				2 => '{"test":"2"}',
			],
		], $channelMock->acks, 'ACKs data');
	}

	public function testConsumeMaxMessages(): void
	{
		$client = $this->createClient(10);

		$channelMock = new ChannelMock();
		$channelMock->setClient($client);

		$connectionMock = \Mockery::mock(IConnection::class)
			->shouldReceive('getChannel')->andReturn($channelMock)->getMock();

		$queueMock = \Mockery::mock(IQueue::class)
			->shouldReceive('getConnection')->andReturn($connectionMock)->getMock()
			->shouldReceive('getName')->andReturn('testQueue')->getMock();

		$countOfConsumerCallbackCalls = 0;
		$callback = function (Message $message) use (&$countOfConsumerCallbackCalls) {
			$countOfConsumerCallbackCalls++;
			Assert::same('{"test":"' . $countOfConsumerCallbackCalls . '"}', $message->content, 'Consume message - content');
			Assert::same((string) $countOfConsumerCallbackCalls, $message->deliveryTag, 'Consume message - deliveryTag');

			return IConsumer::MESSAGE_ACK;
		};

		$instance = new Consumer('bulkTest', $queueMock, $callback, null, null);

		$instance->consume(null, 5);
		Assert::same(5, $countOfConsumerCallbackCalls, 'Number of consumer callback calls');
		Assert::count(1, $channelMock->acks, 'Number of ACKs');
		Assert::same([
			1 => [
				1 => '{"test":"1"}',
				2 => '{"test":"2"}',
				3 => '{"test":"3"}',
				4 => '{"test":"4"}',
				5 => '{"test":"5"}',
			],
		], $channelMock->acks, 'ACKs data');
	}

	public function testConsumeMessagesException(): void
	{
		$client = $this->createClient();

		$channelMock = new ChannelMock();
		$channelMock->setClient($client);

		$connectionMock = \Mockery::mock(IConnection::class)
			->shouldReceive('getChannel')->andReturn($channelMock)->getMock();

		$queueMock = \Mockery::mock(IQueue::class)
			->shouldReceive('getConnection')->andReturn($connectionMock)->getMock()
			->shouldReceive('getName')->andReturn('testQueue')->getMock();

		$countOfConsumerCallbackCalls = 0;
		$callback = function (Message $message) use (&$countOfConsumerCallbackCalls): void {
			$countOfConsumerCallbackCalls++;
			Assert::same('{"test":"1"}', $message->content, 'Consume message - content');
			Assert::same('1', $message->deliveryTag, 'Consume message - deliveryTag');

			throw new \Exception('test-exc');
		};

		$instance = new Consumer('bulkTest', $queueMock, $callback, null, null);

		Assert::exception(fn () => $instance->consume(2), \Throwable::class, 'test-exc');

		Assert::same(1, $countOfConsumerCallbackCalls, 'Number of consumer callback calls');
		Assert::count(0, $channelMock->nacks, 'Number of NACKs');
		Assert::same([], $channelMock->nacks, 'NACKs data');
	}

	public function testConsumeMessagesBadResultTypeError(): void
	{
		$client = $this->createClient();

		$channelMock = new ChannelMock();
		$channelMock->setClient($client);

		$connectionMock = \Mockery::mock(IConnection::class)
			->shouldReceive('getChannel')->andReturn($channelMock)->getMock();

		$queueMock = \Mockery::mock(IQueue::class)
			->shouldReceive('getConnection')->andReturn($connectionMock)->getMock()
			->shouldReceive('getName')->andReturn('testQueue')->getMock();

		$countOfConsumerCallbackCalls = 0;
		$callback = function (Message $message) use (&$countOfConsumerCallbackCalls) {
			$countOfConsumerCallbackCalls++;
			Assert::same('{"test":"1"}', $message->content, 'Consume message - content');
			Assert::same('1', $message->deliveryTag, 'Consume message - deliveryTag');

			return true;
		};

		$instance = new Consumer('bulkTest', $queueMock, $callback, null, null);

		Assert::exception(fn () => $instance->consume(1), \TypeError::class);

		Assert::same(1, $countOfConsumerCallbackCalls, 'Number of consumer callback calls');
		Assert::count(0, $channelMock->nacks, 'Number of NACKs');
		Assert::same([], $channelMock->nacks, 'NACKs data');
	}

	public function testConsumeMessagesBadResultinvalidArgumetException(): void
	{
		$client = $this->createClient();

		$channelMock = new ChannelMock();
		$channelMock->setClient($client);

		$connectionMock = \Mockery::mock(IConnection::class)
			->shouldReceive('getChannel')->andReturn($channelMock)->getMock();

		$queueMock = \Mockery::mock(IQueue::class)
			->shouldReceive('getConnection')->andReturn($connectionMock)->getMock()
			->shouldReceive('getName')->andReturn('testQueue')->getMock();

		$countOfConsumerCallbackCalls = 0;
		$callback = function (Message $message) use (&$countOfConsumerCallbackCalls) {
			$countOfConsumerCallbackCalls++;
			Assert::same('{"test":"1"}', $message->content, 'Consume message - content');
			Assert::same('1', $message->deliveryTag, 'Consume message - deliveryTag');

			return PHP_INT_MAX - 987654321;
		};

		$instance = new Consumer('bulkTest', $queueMock, $callback, null, null);

		Assert::exception(fn () => $instance->consume(1), \InvalidArgumentException::class);

		Assert::same(1, $countOfConsumerCallbackCalls, 'Number of consumer callback calls');
		Assert::count(0, $channelMock->nacks, 'Number of NACKs');
		Assert::same([], $channelMock->nacks, 'NACKs data');
	}

	protected function createClient(int $numberOfMessages = 2)
	{
		$messages = [];
		for ($i = 1; $i <= $numberOfMessages; $i++) {
			$messages[] = ['key' => (string) $i, 'content' => '{"test":"' . $i . '"}'];
		}

		return new class($messages) extends Client {

			private array $dataToConsume;

			private $callback;

			private $channel;

			public function __construct($dataToConsume)
			{
				$this->dataToConsume = $dataToConsume;
			}

			public function setCallback($callback): void
			{
				$this->callback = $callback;
			}

			public function setChannel($channel): void
			{
				$this->channel = $channel;
			}

			public function disconnect($replyCode = 0, $replyText = ''): void
			{
			}

			public function run($maxSeconds = null): void
			{
				$this->channel->ackPos++;
				$this->channel->nackPos++;
				if (count($this->dataToConsume) > 0) {
					$this->running = true;
					do {
						$data = array_shift($this->dataToConsume);
						if ($data !== null) {
							call_user_func($this->callback, new Message($data['key'], $data['key'], false, 'consumerTest', '', [], $data['content']), $this->channel, $this);
						}
					} while ($this->running && $data !== null);
				}
			}

			protected function feedReadBuffer(): void
			{
			}

			protected function flushWriteBuffer(): void
			{
			}

		};
	}

}

(new ConsumerTest())->run();
