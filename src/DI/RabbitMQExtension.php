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
use Gamee\RabbitMQ\Console\Command\StaticConsumerCommand;
use Gamee\RabbitMQ\DI\Helpers\ConnectionsHelper;
use Gamee\RabbitMQ\DI\Helpers\ConsumersHelper;
use Gamee\RabbitMQ\DI\Helpers\ExchangesHelper;
use Gamee\RabbitMQ\DI\Helpers\ProducersHelper;
use Gamee\RabbitMQ\DI\Helpers\QueuesHelper;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;

final class RabbitMQExtension extends CompilerExtension
{

	/**
	 * @var array
	 */
	private $defaults = [
		'connections' => [],
		'queues' => [],
		'exchanges' => [],
		'producers' => [],
		'consumers' => [],
	];

	/**
	 * @var ConnectionsHelper
	 */
	private $connectionsHelper;

	/**
	 * @var QueuesHelper
	 */
	private $queuesHelper;

	/**
	 * @var ProducersHelper
	 */
	private $producersHelper;

	/**
	 * @var ExchangesHelper
	 */
	private $exchangesHelper;

	/**
	 * @var ConsumersHelper
	 */
	private $consumersHelper;


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
		$config = $this->validateConfig($this->defaults, $this->getConfig());
		$builder = $this->getContainerBuilder();

		/**
		 * Connections
		 * @var ServiceDefinition
		 */
		$connectionFactory = $this->connectionsHelper->setup($builder, $config['connections']);

		/**
		 * Queues
		 * @var ServiceDefinition
		 */
		$queueFactory = $this->queuesHelper->setup($builder, $config['queues']);

		/**
		 * Exchanges
		 * @var ServiceDefinition
		 */
		$exchangeFactory = $this->exchangesHelper->setup($builder, $config['exchanges']);

		/**
		 * Producers
		 * @var ServiceDefinition
		 */
		$producerFactory = $this->producersHelper->setup($builder, $config['producers']);

		/**
		 * Consumers
		 * @var ServiceDefinition
		 */
		$consumerFactory = $this->consumersHelper->setup($builder, $config['consumers']);

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

		$consumerCommand = $builder->addDefinition($this->prefix('console.consumerCommand'))
			->setFactory(ConsumerCommand::class);

		$staticConsumerCommand = $builder->addDefinition($this->prefix('console.staticConsumerCommand'))
			->setFactory(StaticConsumerCommand::class);

		if (class_exists('Kdyby\Console\DI\ConsoleExtension')) {
			$consumerCommand->addTag(\Kdyby\Console\DI\ConsoleExtension::TAG_COMMAND);
			$staticConsumerCommand->addTag(\Kdyby\Console\DI\ConsoleExtension::TAG_COMMAND);
		}
	}

}
