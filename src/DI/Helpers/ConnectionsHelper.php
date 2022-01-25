<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Contributte\RabbitMQ\Connection\ConnectionFactory;
use Contributte\RabbitMQ\Connection\ConnectionsDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class ConnectionsHelper extends AbstractHelper
{
	public function getConfigSchema(): Schema {
		return Expect::arrayOf(
			Expect::structure([
				'user' => Expect::string('guest'),
				'password' => Expect::string('guest')->dynamic(),
				'host' => Expect::string('127.0.0.1'),
				'port' => Expect::int(5672),
				'vhost' => Expect::string('/'),
				'path' => Expect::string('/'),
				'timeout' => Expect::anyOf(Expect::float(), Expect::int())->default(10)->castTo('float'),
				'heartbeat' => Expect::anyOf(Expect::float(), Expect::int())->default(60)->castTo('float'),
				'persistent' => Expect::bool(false),
				'tcpNoDelay' => Expect::bool(false),
				'lazy' => Expect::bool(true),
				'ssl' => Expect::array(null)->required(false),
				'admin' => Expect::structure([
					'port' => Expect::int(15672),
					'secure' => Expect::bool(false),
				])->castTo('array')->required(false),
			])->castTo('array'),
			'string'
		);
	}

	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$connectionsDataBag = $builder->addDefinition($this->extension->prefix('connectionsDataBag'))
			->setFactory(ConnectionsDataBag::class)
			->setArguments([$config]);

		return $builder->addDefinition($this->extension->prefix('connectionFactory'))
			->setFactory(ConnectionFactory::class)
			->setArguments([$connectionsDataBag]);
	}

}
