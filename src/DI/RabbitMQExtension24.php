<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\DI;

use Contributte\RabbitMQ\Client;
use Contributte\RabbitMQ\Console\Command\ConsumerCommand;
use Contributte\RabbitMQ\Console\Command\DeclareQueuesAndExchangesCommand;
use Contributte\RabbitMQ\Console\Command\StaticConsumerCommand;
use Contributte\RabbitMQ\DI\Helpers\ConnectionsHelper;
use Contributte\RabbitMQ\DI\Helpers\ConsumersHelper;
use Contributte\RabbitMQ\DI\Helpers\ExchangesHelper;
use Contributte\RabbitMQ\DI\Helpers\ProducersHelper;
use Contributte\RabbitMQ\DI\Helpers\QueuesHelper;
use Nette\DI\CompilerExtension;

final class RabbitMQExtension24 extends CompilerExtension
{

	/** @var array<string, mixed> */
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

	public function loadConfiguration(): void
	{
		$config = $this->validateConfig($this->defaults); // @phpstan-ignore-line
		$builder = $this->getContainerBuilder();

		$this->connectionsHelper->setup($builder, $config['connections']);
		$this->queuesHelper->setup($builder, $config['queues']);
		$this->exchangesHelper->setup($builder, $config['exchanges']);
		$this->producersHelper->setup($builder, $config['producers']);
		$this->consumersHelper->setup($builder, $config['consumers']);

		// Register Client class
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
