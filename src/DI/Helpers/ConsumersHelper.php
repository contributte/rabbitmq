<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Contributte\RabbitMQ\Consumer\ConsumerFactory;
use Contributte\RabbitMQ\Consumer\ConsumersDataBag;
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
		'bulk' => [
			'size' => null,
			'timeout' => null,
		],
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
