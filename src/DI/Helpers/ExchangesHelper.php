<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Contributte\RabbitMQ\Exchange\ExchangeDeclarator;
use Contributte\RabbitMQ\Exchange\ExchangeFactory;
use Contributte\RabbitMQ\Exchange\ExchangesDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class ExchangesHelper extends AbstractHelper
{

	public const EXCHANGE_TYPES = ['direct', 'topic', 'headers', 'fanout'];

	public function getConfigSchema(): Schema
	{
		return Expect::arrayOf(
			Expect::structure([
				'connection' => Expect::string('default'),
				'type' => Expect::anyOf(...self::EXCHANGE_TYPES)->default(self::EXCHANGE_TYPES[0]),
				'passive' => Expect::bool(false),
				'durable' => Expect::bool(true),
				'autoDelete' => Expect::bool(false),
				'internal' => Expect::bool(false),
				'noWait' => Expect::bool(false),
				'arguments' => Expect::array(),
				'queueBindings' => Expect::arrayOf(
					Expect::structure([
						'routingKey' => Expect::string(''),
						'noWait' => Expect::bool(false),
						'arguments' => Expect::array(),
					])->castTo('array'),
					'string'
				)->default([]),
				'federation' => Expect::structure([
					'uri' => Expect::string()->required()->dynamic(),
					'prefetchCount' => Expect::int(20)->min(1),
					'reconnectDelay' => Expect::int(1)->min(1),
					'messageTTL' => Expect::int(3600000)->min(1),
					'expires' => Expect::int(3600000)->min(1),
					'ackMode' => Expect::anyOf('on-confirm', 'on-publish', 'no-ack')->default('no-ack'),
					'policy' => Expect::structure([
						'priority' => Expect::int(0),
						'arguments' => Expect::arrayOf(
							Expect::anyOf(Expect::string(), Expect::int(), Expect::bool()),
							'string'
						)->default([])->before(fn ($arguments) => $this->normalizePolicyArguments($arguments)),
					])->castTo('array'),
				])->castTo('array')->required(false),
				'autoCreate' => Expect::int(2)->before(fn ($input) => $input === 'lazy' ? 2 : (int) $input),
			])->castTo('array'),
			'string'
		);
	}


	/**
	 * @throws \InvalidArgumentException
	 */
	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$exchangesDataBag = $builder->addDefinition($this->extension->prefix('exchangesDataBag'))
			->setFactory(ExchangesDataBag::class)
			->setArguments([$config]);

		$builder->addDefinition($this->extension->prefix('exchangesDeclarator'))
			->setFactory(ExchangeDeclarator::class);

		return $builder->addDefinition($this->extension->prefix('exchangeFactory'))
			->setFactory(ExchangeFactory::class)
			->setArguments([$exchangesDataBag]);
	}

	protected function normalizePolicyArguments(array $arguments = []): array
	{
		$return = [];
		foreach($arguments as $key => $value) {
			$return[$this->normalizePolicyArgumentKey($key)] = $value;
		}

		return $return;
	}

	private function normalizePolicyArgumentKey(string $key): string
	{
		return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^-])([A-Z][a-z])/'], '$1-$2', $key));
	}
}
