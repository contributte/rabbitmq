<?php declare(strict_types = 1);

namespace Tests\Cases;

use Bunny\Client;
use Bunny\Message;
use Contributte\RabbitMQ\Connection\IConnection;
use Contributte\RabbitMQ\Consumer\BulkConsumer;
use Contributte\RabbitMQ\Consumer\Exception\UnexpectedConsumerResultTypeException;
use Contributte\RabbitMQ\Consumer\IConsumer;
use Contributte\RabbitMQ\Queue\IQueue;
use Tester\Assert;
use Tester\TestCase;
use Tests\Fixtures\ChannelMock;

require __DIR__ . '/../bootstrap.php';

final class BulkConsumerTest extends TestCase
{

	public function testConsumeMessagesToLimit(): void
	{
		$client = $this->createClient();

		$channelMock = new ChannelMock();
		$channelMock->setClient($client);

		$connectionMock = \Mockery::mock(IConnection::class)
			->shouldReceive('getChannel')->andReturn($channelMock)->getMock();

		$queueMock = \Mockery::mock(IQueue::class)
			->shouldReceive('getConnection')->andReturn($connectionMock)->getMock()
			->shouldReceive('getName')->andReturn('testQueue')->getMock();

		$counter = (object) ['value' => 0];
		$callback = function (array $messages) use ($counter): array {
			$counter->value++;

			return array_map(fn (Message $message): int => IConsumer::MESSAGE_ACK, $messages);
		};

		$instance = new BulkConsumer('bulkTest', $queueMock, $callback, null, null, 3, 2);

		$instance->consume(2);

		Assert::same(2, $counter->value, 'Number of consumer callback calls');
		Assert::count(2, $channelMock->acks, 'Number of ACKs');
		Assert::same([
			1 => [
				1 => '{"test":"1"}',
				2 => '{"test":"2"}',
				3 => '{"test":"3"}',
			],
			2 => [
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

		$counter = (object) ['value' => 0];
		$callback = function (array $messages) use ($counter): void {
			$counter->value++;

			throw new \Exception('test');
		};

		$instance = new BulkConsumer('bulkTest', $queueMock, $callback, null, null, 3, 2);

		$instance->consume(2);

		Assert::same(2, $counter->value, 'Number of consumer callback calls');
		Assert::count(2, $channelMock->nacks, 'Number of NACKs');
		Assert::same([
			1 => [
				1 => '{"test":"1"}',
				2 => '{"test":"2"}',
				3 => '{"test":"3"}',
			],
			2 => [
				4 => '{"test":"4"}',
				5 => '{"test":"5"}',
			],
		], $channelMock->nacks, 'NACKs data');
	}

	public function testConsumeMessagesBadResult(): void
	{
		$client = $this->createClient();

		$channelMock = new ChannelMock();
		$channelMock->setClient($client);

		$connectionMock = \Mockery::mock(IConnection::class)
			->shouldReceive('getChannel')->andReturn($channelMock)->getMock();

		$queueMock = \Mockery::mock(IQueue::class)
			->shouldReceive('getConnection')->andReturn($connectionMock)->getMock()
			->shouldReceive('getName')->andReturn('testQueue')->getMock();

		$counter = (object) ['value' => 0];
		$callback = function (array $messages) use ($counter): bool {
			$counter->value++;

			return true;
		};

		$instance = new BulkConsumer('bulkTest', $queueMock, $callback, null, null, 3, 2);

		Assert::exception(fn () => $instance->consume(2), UnexpectedConsumerResultTypeException::class);

		Assert::same(1, $counter->value, 'Number of consumer callback calls');
		Assert::count(1, $channelMock->nacks, 'Number of NACKs');
		Assert::same([
			1 => [
				1 => '{"test":"1"}',
				2 => '{"test":"2"}',
				3 => '{"test":"3"}',
			],
		], $channelMock->nacks, 'NACKs data');
	}

	protected function createClient(): Client
	{
		return new class([
			['key' => '1', 'content' => '{"test":"1"}'],
			['key' => '2', 'content' => '{"test":"2"}'],
			['key' => '3', 'content' => '{"test":"3"}'],
			['key' => '4', 'content' => '{"test":"4"}'],
			['key' => '5', 'content' => '{"test":"5"}'],
		]) extends Client {

			/** @var array<array{key: string, content: string}> */
			private array $dataToConsume;

			/** @var callable|null */
			private mixed $callback = null;

			private ?ChannelMock $channel = null;

			/**
			 * @param array<array{key: string, content: string}> $dataToConsume
			 */
			public function __construct(array $dataToConsume)
			{
				$this->dataToConsume = $dataToConsume;
			}

			public function setCallback(callable $callback): void
			{
				$this->callback = $callback;
			}

			public function setChannel(ChannelMock $channel): void
			{
				$this->channel = $channel;
			}

			/**
			 * @param int $replyCode
			 * @param string $replyText
			 */
			public function disconnect($replyCode = 0, $replyText = ''): void
			{
				// intentionally empty - mock
			}

			/**
			 * @param float|null $maxSeconds
			 */
			public function run($maxSeconds = null): void
			{
				$this->channel->ackPos++;
				$this->channel->nackPos++;
				if (count($this->dataToConsume) > 0) {
					$this->running = true;
					do {
						$data = array_shift($this->dataToConsume);
						if ($data !== null) {
							call_user_func($this->callback, new Message($data['key'], $data['key'], false, 'bulkTest', '', [], $data['content']), $this->channel, $this);
						}
					} while ($this->running && $data !== null);
				}
			}

			protected function feedReadBuffer(): void
			{
				// intentionally empty - mock
			}

			protected function flushWriteBuffer(): void
			{
				// intentionally empty - mock
			}

		};
	}

}

(new BulkConsumerTest())->run();
