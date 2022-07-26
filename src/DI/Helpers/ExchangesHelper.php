<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Contributte\RabbitMQ\Exchange\ExchangeDeclarator;
use Contributte\RabbitMQ\Exchange\ExchangeFactory;
use Contributte\RabbitMQ\Exchange\ExchangesDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;

final class ExchangesHelper extends AbstractHelper
{

	public const EXCHANGE_TYPES = ['direct', 'topic', 'headers', 'fanout', 'x-delayed-message'];

	/**
	 * @var array
	 */
	protected array $defaults = [
		'connection' => 'default',
		// direct/topic/headers/fanout
		'type' => 'direct',
		'passive' => false,
		'durable' => true,
		'autoDelete' => false,
		'internal' => false,
		'noWait' => false,
		'arguments' => [],
		// See self::$queueBindingDefaults
		'queueBindings' => [],
		'autoCreate' => false,
	];

	/**
	 * @var array
	 */
	private array $queueBindingDefaults = [
		'routingKey' => '',
		'routingKeys' => [],
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

			if ($exchangeConfig['queueBindings'] !== []) {
				foreach ($exchangeConfig['queueBindings'] as $queueName => $queueBindingData) {
					if (isset($queueBindingData['routingKey']) && isset($queueBindingData['routingKeys'])) {
						throw new \InvalidArgumentException(
							"Options `routingKey` and `routingKeys` cannot be specified at the same time"
						);
					}

					$queueBindingConfig = $this->extension->validateConfig(
						$this->queueBindingDefaults,
						$queueBindingData
					);

					$queueBindingConfig['routingKey'] = (string) $queueBindingConfig['routingKey'];
					$queueBindingConfig['routingKeys'] = array_map('strval', (array) $queueBindingConfig['routingKeys']);

					$exchangeConfig['queueBindings'][$queueName] = $queueBindingConfig;
				}
			}

			$exchangesConfig[$exchangeName] = $exchangeConfig;
		}

		$exchangesDataBag = $builder->addDefinition($this->extension->prefix('exchangesDataBag'))
			->setFactory(ExchangesDataBag::class)
			->setArguments([$exchangesConfig]);

		$builder->addDefinition($this->extension->prefix('exchangesDeclarator'))
			->setFactory(ExchangeDeclarator::class);

		return $builder->addDefinition($this->extension->prefix('exchangeFactory'))
			->setFactory(ExchangeFactory::class)
			->setArguments([$exchangesDataBag]);
	}
}
