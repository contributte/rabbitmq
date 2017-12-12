<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI\Helpers;

use Gamee\RabbitMQ\Producer\Producer;
use Gamee\RabbitMQ\Producer\ProducerFactory;
use Gamee\RabbitMQ\Producer\ProducersDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;

final class ProducersHelper extends AbstractHelper
{

	public const DELIVERY_MODES = [
		Producer::DELIVERY_MODE_PERSISTENT,
		Producer::DELIVERY_MODE_PERSISTENT,
	];

	/**
	 * @var array
	 */
	protected $defaults = [
		'exchange' => null,
		'queue' => null,
		'contentType' => 'text/plain',
		'deliveryMode' => Producer::DELIVERY_MODE_PERSISTENT,
	];


	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$producersConfig = [];

		foreach ($config as $producerName => $producerData) {
			$producerConfig = $this->extension->validateConfig(
				$this->getDefaults(),
				$producerData
			);

			if (!in_array($producerConfig['deliveryMode'], self::DELIVERY_MODES, true)) {
				throw new \InvalidArgumentException(
					"Unknown exchange type [{$producerConfig['type']}]"
				);
			}

			/**
			 * 1, Producer has to be subscribed to either a queue or an exchange
			 * 2, A producer can be subscribed to both a queue and an exchange
			 */
			if (empty($producerConfig['queue']) && empty($producerConfig['exchange'])) {
				throw new \InvalidArgumentException(
					'Producer has to be subscribed to either a queue or an exchange'
				);
			}

			$producersConfig[$producerName] = $producerConfig;
		}

		$producersDataBag = $builder->addDefinition($this->extension->prefix('producersDataBag'))
			->setFactory(ProducersDataBag::class)
			->setArguments([$producersConfig]);

		return $builder->addDefinition($this->extension->prefix('producerFactory'))
			->setFactory(ProducerFactory::class)
			->setArguments([$producersDataBag]);
	}

}
