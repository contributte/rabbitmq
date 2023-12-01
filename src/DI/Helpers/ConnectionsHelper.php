<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Contributte\RabbitMQ\Connection\ConnectionFactory;
use Contributte\RabbitMQ\Connection\ConnectionsDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;

final class ConnectionsHelper extends AbstractHelper
{

	/** @var array<string, mixed> */
	protected array $defaults = [
		'host' => '127.0.0.1',
		'port' => 5672,
		'user' => 'guest',
		'password' => 'guest',
		'vhost' => '/',
		'timeout' => 1,
		'heartbeat' => 60.0,
		'persistent' => false,
		'path' => '/',
		'tcpNoDelay' => false,
		'lazy' => false,
		'ssl' => null,
	];

	/**
	 * @param array<string, mixed> $config
	 */
	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$connectionsConfig = [];

		foreach ($config as $connectionName => $connectionData) {
			// @phpstan-ignore-next-line
			$connectionsConfig[$connectionName] = $this->extension->validateConfig(
				$this->getDefaults(),
				$connectionData
			);
		}

		$connectionsDataBag = $builder->addDefinition($this->extension->prefix('connectionsDataBag'))
			->setFactory(ConnectionsDataBag::class)
			->setArguments([$connectionsConfig]);

		return $builder->addDefinition($this->extension->prefix('connectionFactory'))
			->setFactory(ConnectionFactory::class)
			->setArguments([$connectionsDataBag]);
	}

}
