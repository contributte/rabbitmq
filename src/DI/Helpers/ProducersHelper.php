<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI\Helpers;

use Gamee\RabbitMQ\Producer\ProducersDataBag;
use Gamee\RabbitMQ\Producer\Producer;
use Gamee\RabbitMQ\Producer\ProducerFactory;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;

final class ProducersHelper extends AbstractHelper
{

	/**
	 * @var array
	 */
	protected $defaults = [
		'connection' => 'default',
		'exchange' => [],
		'queue' => [],
		'contentType' => 'text/plain',
		'deliveryMode' => Producer::DELIVERY_MODE_PERSISTENT
	];


	public function setup(
		ContainerBuilder $builder,
		array $config = [],
		ServiceDefinition $connectionFactoryDefinition
	): ServiceDefinition {
		$producersConfig = [];

		foreach ($config as $producerName => $producerData) {
			$producersConfig[$producerName] = $this->extension->validateConfig(
				$this->getDefaults(),
				$producerData
			);
		}

		$producersDataBag = $builder->addDefinition($this->extension->prefix('producersDataBag'))
			->setClass(ProducersDataBag::class)
			->setArguments([$producersConfig]);

		return $builder->addDefinition($this->extension->prefix('producerFactory'))
			->setClass(ProducerFactory::class)
			->setArguments([$producersDataBag, $connectionFactoryDefinition]);
	}

}
