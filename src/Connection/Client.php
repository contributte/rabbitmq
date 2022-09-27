<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

use Bunny\Client as BunnyClient;
use Bunny\ClientStateEnum;
use Bunny\Constants;
use Bunny\Exception\BunnyException;
use Bunny\Exception\ClientException;
use Bunny\Protocol;
use Contributte\RabbitMQ\Connection\Exception\WaitTimeoutException;
use Nette\Utils\Strings;
use function time;

/**
 * @codeCoverageIgnore
 */
class Client extends BunnyClient
{
	/**
	 * Constructor.
	 *
	 * @param array<string|mixed> $options
	 */
	public function __construct(array $options = [])
	{
		parent::__construct($options);

		$this->options['cycle_callback'] = is_callable($options['cycle_callback'])
			? $options['cycle_callback']
			: null;
		$this->options['heartbeat_callback'] = is_callable($options['heartbeat_callback'])
			? $options['heartbeat_callback']
			: null;
	}

	/**
	 * @throws BunnyException
	 */
	public function sendHeartbeat(): void
	{
		$this->getWriter()->appendFrame(new Protocol\HeartbeatFrame(), $this->writeBuffer);
		$this->flushWriteBuffer();

		$this->options['heartbeat_callback'] && $this->options['heartbeat_callback']();
	}

	public function syncDisconnect(int $replyCode = 0, string $replyText = ""): bool
	{
		try {
			if ($this->state !== ClientStateEnum::CONNECTED) {
				return false;
			}

			$this->state = ClientStateEnum::DISCONNECTING;

			foreach ($this->channels as $channel) {
				$channelId = $channel->getChannelId();

				$this->channelClose($channelId, $replyCode, $replyText, 0, 0);
				$this->removeChannel($channelId);
			}

			$this->connectionClose($replyCode, $replyText, 0, 0);
			$this->closeStream();
		} catch (ClientException) {
			// swallow, we do not care we are not connected, we want to close connection anyway
		}

		$this->init();

		return true;
	}

	protected function write(): void
	{
		if ($this->stream && feof($this->stream)) {
			$this->syncDisconnect(Constants::STATUS_RESOURCE_ERROR, "Connection closed by server unexpectedly");
			throw new ClientException("Broken pipe or closed connection.");
		}

		parent::write();

		if (($last = error_get_last()) !== null) {
			if (!Strings::match($last['message'], '~fwrite\(\): Send of \d+ bytes failed with errno=\d+ broken pipe~i')) {
				return;
			}

			error_clear_last();
			throw new ClientException('Broken pipe or closed connection.');
		}
	}

	/**
	 * Runs it's own event loop, processes frames as they arrive. Processes messages for at most $maxSeconds.
	 *
	 * @param float $maxSeconds
	 */
	public function run($maxSeconds = null): void
	{
		if (!$this->isConnected()) {
			throw new ClientException('Client has to be connected.');
		}

		$this->running = true;
		$startTime = microtime(true);
		$stopTime = null;
		if ($maxSeconds !== null) {
			$stopTime = $startTime + $maxSeconds;
		}

		do {
			$this->options['cycle_callback'] && $this->options['cycle_callback']();
			if (!empty($this->queue)) {
				$frame = array_shift($this->queue);
			} else {
				/** @var Protocol\AbstractFrame|null $frame */
				$frame = $this->reader->consumeFrame($this->readBuffer);
				if ($frame === null) {
					$now = microtime(true);
					$nextStreamSelectTimeout = ($this->lastWrite ?: $now) + $this->options["heartbeat"];
					if ($stopTime !== null && $stopTime < $nextStreamSelectTimeout) {
						$nextStreamSelectTimeout = $stopTime;
					}
					$tvSec = max((int)($nextStreamSelectTimeout - $now), 0);
					$tvUsec = max((int)(($nextStreamSelectTimeout - $now - $tvSec) * 1000000), 0);

					$r = [$this->getStream()];
					$w = null;
					$e = null;

					if (($n = @stream_select($r, $w, $e, $tvSec, $tvUsec)) === false) {
						$lastError = error_get_last();
						if ($lastError !== null &&
							preg_match('/^stream_select\\(\\): unable to select \\[(\\d+)\\]:/', $lastError['message'], $m) &&
							(int)$m[1] === PCNTL_EINTR
						) {
							// got interrupted by signal, dispatch signals & continue
							pcntl_signal_dispatch();
							$n = 0;
						} else {
							throw new ClientException(sprintf(
								'stream_select() failed: %s',
								$lastError ? $lastError['message'] : 'Unknown error.'
							));
						}
					}
					$now = microtime(true);
					if ($stopTime !== null && $now >= $stopTime) {
						break;
					}

					if ($n > 0) {
						$this->feedReadBuffer();
					}

					continue;
				}
			}

			if ($frame->channel === 0) {
				$this->onFrameReceived($frame);
			} else {
				if (!isset($this->channels[$frame->channel])) {
					throw new ClientException(
						"Received frame #{$frame->type} on closed channel #{$frame->channel}."
					);
				}

				$this->channels[$frame->channel]->onFrameReceived($frame);
			}
		} while ($this->running);
	}

	public function waitForConfirm(int $channel, ?int $timeout = null): Protocol\MethodBasicAckFrame|Protocol\MethodBasicNackFrame
	{
		$frame = null; // psalm bug
		$time = time();

		$checkTimeout = static function() use ($time, $timeout): void {
			if ($timeout && $time + $timeout < time()) {
				throw new WaitTimeoutException('Timeout reached');
			}
		};

		while (true) {
			$checkTimeout();

			/**
			 * @phpstan-ignore-next-line
			 */
			while (($frame = $this->getReader()->consumeFrame($this->getReadBuffer())) === null) {
				$this->feedReadBuffer();
				$checkTimeout();
			}

			if ($frame->channel === $channel && ($frame instanceof Protocol\MethodBasicNackFrame || $frame instanceof Protocol\MethodBasicAckFrame)) {
				return $frame;
			}

			if ($frame instanceof Protocol\MethodChannelCloseFrame && $frame->channel === $channel) {
				$this->channelCloseOk($channel);
				throw new ClientException($frame->replyText, $frame->replyCode);
			}

			if ($frame instanceof Protocol\MethodConnectionCloseFrame) {
				$this->connectionCloseOk();
				throw new ClientException($frame->replyText, $frame->replyCode);
			}

			$this->enqueue($frame);
		}
	}
}
