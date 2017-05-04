<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI\Helpers;

use Gamee\RabbitMQ\Exchange\ExchangeFactory;
use Gamee\RabbitMQ\Exchange\ExchangesDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;

final class ExchangesHelper extends AbstractHelper
{

	/**
	 * @var array
	 */
	protected $defaults = [
		'type' => 'direct',
		'passive' => FALSE,
		'durable' => TRUE,
		'autoDelete' => FALSE,
		'internal' => FALSE,
		'noWait' => FALSE,
		'arguments' => []
	];


	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$exchangesConfig = [];

		foreach ($config as $exchangeName => $exchangeData) {
			$exchangesConfig[$exchangeName] = $this->extension->validateConfig(
				$this->getDefaults(),
				$exchangeData
			);
		}

		$exchangesDataBag = $builder->addDefinition($this->extension->prefix('exchangesDataBag'))
			->setClass(ExchangesDataBag::class)
			->setArguments([$exchangesConfig]);

		return $builder->addDefinition($this->extension->prefix('exchangeFactory'))
			->setClass(ExchangeFactory::class)
			->setArguments([$exchangesDataBag]);
	}

}
