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

	const EXCHANGE_TYPES = ['direct', 'topic', 'headers', 'fanout'];

	/**
	 * @var array
	 */
	protected $defaults = [
		'type' => 'direct', // direct/topic/headers/fanout
		'passive' => FALSE,
		'durable' => TRUE,
		'autoDelete' => FALSE,
		'internal' => FALSE,
		'noWait' => FALSE,
		'arguments' => [],
		'queueBindings' => [] // See self::$queueBindingDefaults
	];

	/**
	 * @var array
	 */
	private $queueBindingDefaults = [
		'routingKey' => '',
		'noWait' => FALSE,
		'arguments' => []
	];


	/**
	 * @throws \InvalidArgumentException
	 */
	public function setup(
		ContainerBuilder $builder,
		array $config = [],
		ServiceDefinition $queueFactory
	): ServiceDefinition {
		$exchangesConfig = [];

		foreach ($config as $exchangeName => $exchangeData) {
			$exchangeConfig = $this->extension->validateConfig(
				$this->getDefaults(),
				$exchangeData
			);

			/**
			 * Validate exchange type
			 */
			if (!in_array($exchangeConfig['type'], self::EXCHANGE_TYPES)) {
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
			->setClass(ExchangesDataBag::class)
			->setArguments([$exchangesConfig]);

		return $builder->addDefinition($this->extension->prefix('exchangeFactory'))
			->setClass(ExchangeFactory::class)
			->setArguments([$exchangesDataBag, $queueFactory]);
	}

}
