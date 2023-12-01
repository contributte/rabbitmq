<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Exchange;

use Contributte\RabbitMQ\AbstractDataBag;

final class ExchangesDataBag extends AbstractDataBag
{

	/**
	 * @param array<string, mixed> $config
	 */
	public function addExchangeConfig(string $exchangeName, array $config): void
	{
		$this->data[$exchangeName] = $config;
	}

}
