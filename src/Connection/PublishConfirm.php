<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

class PublishConfirm
{
	public function __construct(
		private bool $isAck = false,
		private int $deliveryTag = 0
	) {
	}

	public function isAck(): bool
	{
		return $this->isAck;
	}

	public function deliveryTag(): int
	{
		return $this->deliveryTag;
	}
}
