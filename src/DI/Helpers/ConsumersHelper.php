<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI\Helpers;

use Gamee\RabbitMQ\Consumer\ConsumerFactory;
use Gamee\RabbitMQ\Consumer\ConsumersDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;

final class ConsumersHelper extends AbstractHelper
{

	/**
	 * @var array
	 */
	protected array $defaults = [
		'queue' => null,
		'callback' => null,
		'idleTimeout' => 30,
		'qos' => [
			'prefetchSize' => null, // 0
			'prefetchCount' =>  null, // 50
		],
	];


	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$consumersConfig = [];

		foreach ($config as $consumerName => $consumerData) {
			$consumerConfig = $this->extension->validateConfig(
				$this->getDefaults(),
				$consumerData
			);

			if ($consumerConfig === []) {
				throw new \InvalidArgumentException(
					'Each consumer has to have a <queue> parameter set'
				);
			}

			$consumersConfig[$consumerName] = $consumerConfig;
		}

		$consumersDataBag = $builder->addDefinition($this->extension->prefix('consumersDataBag'))
			->setFactory(ConsumersDataBag::class)
			->setArguments([$consumersConfig]);

		return $builder->addDefinition($this->extension->prefix('consumerFactory'))
			->setFactory(ConsumerFactory::class)
			->setArguments([$consumersDataBag]);
	}
}
