<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Contributte\RabbitMQ\Queue\QueueDeclarator;
use Contributte\RabbitMQ\Queue\QueueFactory;
use Contributte\RabbitMQ\Queue\QueuesDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class QueuesHelper extends AbstractHelper
{
	public function getConfigSchema(): Schema {
		return Expect::arrayOf(
			Expect::structure([
				'connection' => Expect::string('default'),
				'passive' => Expect::bool(false),
				'durable' => Expect::bool(true),
				'exclusive' => Expect::bool(false),
				'autoDelete' => Expect::bool(false),
				'noWait' => Expect::bool(false),
				'arguments' => Expect::array(),
				'autoCreate' => Expect::int(2)->before(fn ($input) => $input === 'lazy' ? 2 : (int) $input),
			])->castTo('array'),
			'string'
		);
	}


	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$queuesDataBag = $builder->addDefinition($this->extension->prefix('queuesDataBag'))
			->setFactory(QueuesDataBag::class)
			->setArguments([$config]);

		$builder->addDefinition($this->extension->prefix('queueDeclarator'))
			->setFactory(QueueDeclarator::class);

		return $builder->addDefinition($this->extension->prefix('queueFactory'))
			->setFactory(QueueFactory::class)
			->setArguments([$queuesDataBag]);
	}

}
