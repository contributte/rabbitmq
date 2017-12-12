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

	public const EXCHANGE_TYPES = ['direct', 'topic', 'headers', 'fanout'];

	/**
	 * @var array
	 */
	protected $defaults = [
		'type' => 'direct', // direct/topic/headers/fanout
		'passive' => false,
		'durable' => true,
		'autoDelete' => false,
		'internal' => false,
		'noWait' => false,
		'arguments' => [],
		'queueBindings' => [], // See self::$queueBindingDefaults
	];

	/**
	 * @var array
	 */
	private $queueBindingDefaults = [
		'routingKey' => '',
		'noWait' => false,
		'arguments' => [],
	];


	/**
	 * @throws \InvalidArgumentException
	 */
	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$exchangesConfig = [];

		foreach ($config as $exchangeName => $exchangeData) {
			$exchangeConfig = $this->extension->validateConfig(
				$this->getDefaults(),
				$exchangeData
			);

			/**
			 * Validate exchange type
			 */
			if (!in_array($exchangeConfig['type'], self::EXCHANGE_TYPES, true)) {
				throw new \InvalidArgumentException(
					"Unknown exchange type [{$exchangeConfig['type']}]"
				);
			}

			if (!empty($exchangeConfig['queueBindings'])) {
				foreach ($exchangeConfig['queueBindings'] as $queueName => $queueBindingData) {
					$queueBindingData['routingKey'] = (string) $queueBindingData['routingKey'];

					$exchangeConfig['queueBindings'][$queueName] = $this->extension->validateConfig(
						$this->queueBindingDefaults,
						$queueBindingData
					);
				}
			}

			$exchangesConfig[$exchangeName] = $exchangeConfig;
		}

		$exchangesDataBag = $builder->addDefinition($this->extension->prefix('exchangesDataBag'))
			->setFactory(ExchangesDataBag::class)
			->setArguments([$exchangesConfig]);

		return $builder->addDefinition($this->extension->prefix('exchangeFactory'))
			->setFactory(ExchangeFactory::class)
			->setArguments([$exchangesDataBag]);
	}

}
