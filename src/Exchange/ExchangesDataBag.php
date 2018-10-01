<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Exchange;

use Gamee\RabbitMQ\AbstractDataBag;

final class ExchangesDataBag extends AbstractDataBag
{

	public function addExchangeConfig(string $exchangeName, array $config): void
	{
		$this->data[$exchangeName] = $config;
	}

}
