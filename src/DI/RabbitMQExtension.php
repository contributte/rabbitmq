<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI;

use Gamee\RabbitMQ\DI\Helpers\ConnectionsHelper;
use Gamee\RabbitMQ\DI\Helpers\ConsumersHelper;
use Gamee\RabbitMQ\DI\Helpers\ProducersHelper;

final class RabbitMQExtension extends Nette\DI\CompilerExtension
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
		$this->connectionsHelper = new ConnectionsHelper;
		$this->producersHelper = new ProducersHelper;
		$this->consumersHelper = new ConsumersHelper;
	}


	/**
	 * @return void
	 */
	public function beforeCompile(): void
	{
		$config = $this->getConfig();

		$builder = $this->getContainerBuilder();

		$this->producersHelper->setupProducers($builder, $config['producers']);
	}

}
