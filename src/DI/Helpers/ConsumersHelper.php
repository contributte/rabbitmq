<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Contributte\RabbitMQ\Consumer\ConsumerFactory;
use Contributte\RabbitMQ\Consumer\ConsumersDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class ConsumersHelper extends AbstractHelper
{
	public function getConfigSchema(): Schema
	{
		return Expect::arrayOf(
			Expect::structure([
				'queue' => Expect::string()->required(true),
				'callback' => Expect::array()->required(true)->assert('is_callable'),
				'idleTimeout' => Expect::int(30),
				'bulk' => Expect::structure([
					'size' => Expect::int()->min(1),
					'timeout' => Expect::int()->min(1),
				])->castTo('array')->required(false),
				'qos' => Expect::structure([
					'prefetchSize' => Expect::int()->nullable(),
					'prefetchCount' => Expect::int()->nullable(),
				])->castTo('array')->required(false),
			])->castTo('array'),
			'string'
		);
	}

	/**
	 * @param array<string, mixed> $config
	 */
	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$consumersDataBag = $builder->addDefinition($this->extension->prefix('consumersDataBag'))
			->setFactory(ConsumersDataBag::class)
			->setArguments([$config]);

		return $builder->addDefinition($this->extension->prefix('consumerFactory'))
			->setFactory(ConsumerFactory::class)
			->setArguments([$consumersDataBag]);
	}
}
