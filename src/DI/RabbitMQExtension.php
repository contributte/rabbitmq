<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI;

use Gamee\RabbitMQ\Client;
use Gamee\RabbitMQ\DI\Helpers\ConnectionsHelper;
use Gamee\RabbitMQ\DI\Helpers\ConsumersHelper;
use Gamee\RabbitMQ\DI\Helpers\ProducersHelper;
use Gamee\RabbitMQ\Queue\QueueFactory;
use Nette\DI\CompilerExtension;

/**
 * @todo Implement queue-exchange routing keys
 */
final class RabbitMQExtension extends CompilerExtension
{

	/**
	 * @var array
	 */
	private $defaults = [
		'connnections' => [],
		'producers' => [],
		'consumers' => []
	];

	/**
	 * @var ConnectionsHelper
	 */
	private $connectionsHelper;

	/**
	 * @var ProducersHelper
	 */
	private $producersHelper;

	/**
	 * @var ConsumersHelper
	 */
	private $consumersHelper;


	public function __construct()
	{
		$this->connectionsHelper = new ConnectionsHelper($this);
		$this->producersHelper = new ProducersHelper($this);
		$this->consumersHelper = new ConsumersHelper($this);
	}


	/**
	 * @return void
	 */
	public function beforeCompile(): void
	{
		$config = $this->getConfig();

		$builder = $this->getContainerBuilder();

		$connectionFactory = $this->connectionsHelper->setup($builder, $config['connections']);
		$producerFactory = $this->producersHelper->setup(
			$builder,
			$config['producers'],
			$connectionFactory
		);
		$consumersFactory = $this->consumersHelper->setup($builder, $config['producers']);

		$builder->addDefinition($this->prefix('queueFactory'))
			->setClass(QueueFactory::class);

		$builder->addDefinition($this->prefix('client'))
			->setClass(Client::class)
			->setArguments([$producerFactory, $connectionFactory, $consumersFactory]);
	}

}
