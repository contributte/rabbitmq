<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI;

use Gamee\RabbitMQ\Client;
use Gamee\RabbitMQ\Console\Command\ConsumerCommand;
use Gamee\RabbitMQ\Console\Command\DeclareQueuesAndExchangesCommand;
use Gamee\RabbitMQ\Console\Command\StaticConsumerCommand;
use Gamee\RabbitMQ\DI\Helpers\ConnectionsHelper;
use Gamee\RabbitMQ\DI\Helpers\ConsumersHelper;
use Gamee\RabbitMQ\DI\Helpers\ExchangesHelper;
use Gamee\RabbitMQ\DI\Helpers\ProducersHelper;
use Gamee\RabbitMQ\DI\Helpers\QueuesHelper;
use Nette\DI\CompilerExtension;

final class RabbitMQExtension extends CompilerExtension
{

	/**
	 * @var array
	 */
	private array $defaults = [
		'connections' => [],
		'queues' => [],
		'exchanges' => [],
		'producers' => [],
		'consumers' => [],
	];

	private ConnectionsHelper $connectionsHelper;

	private QueuesHelper $queuesHelper;

	private ProducersHelper $producersHelper;

	private ExchangesHelper $exchangesHelper;

	private ConsumersHelper $consumersHelper;


	public function __construct()
	{
		$this->connectionsHelper = new ConnectionsHelper($this);
		$this->queuesHelper = new QueuesHelper($this);
		$this->exchangesHelper = new ExchangesHelper($this);
		$this->producersHelper = new ProducersHelper($this);
		$this->consumersHelper = new ConsumersHelper($this);
	}


	/**
	 * @throws \InvalidArgumentException
	 */
	public function loadConfiguration(): void
	{
		$config = $this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		/**
		 * Connections
		 */
		$this->connectionsHelper->setup($builder, $config['connections']);

		/**
		 * Queues
		 */
		$this->queuesHelper->setup($builder, $config['queues']);

		/**
		 * Exchanges
		 */
		$this->exchangesHelper->setup($builder, $config['exchanges']);

		/**
		 * Producers
		 */
		$this->producersHelper->setup($builder, $config['producers']);

		/**
		 * Consumers
		 */
		$this->consumersHelper->setup($builder, $config['consumers']);

		/**
		 * Register Client class
		 */
		$builder->addDefinition($this->prefix('client'))
			->setFactory(Client::class);

		$this->setupConsoleCommand();
	}


	public function setupConsoleCommand(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('console.consumerCommand'))
			->setFactory(ConsumerCommand::class)
			->setTags(['console.command' => 'rabbitmq:consumer']);

		$builder->addDefinition($this->prefix('console.staticConsumerCommand'))
			->setFactory(StaticConsumerCommand::class)
			->setTags(['console.command' => 'rabbitmq:staticConsumer']);

		$builder->addDefinition($this->prefix('console.declareQueuesExchangesCommand'))
			->setFactory(DeclareQueuesAndExchangesCommand::class)
			->setTags(['console.command' => 'rabbitmq:declareQueuesAndExchanges']);
	}
}
