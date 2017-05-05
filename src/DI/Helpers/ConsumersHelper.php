<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI\Helpers;

use Gamee\RabbitMQ\Consumer\ConsumerFactory;
use Gamee\RabbitMQ\Consumer\ConsumersDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;

final class ConsumersHelper extends AbstractHelper
{

	/**
	 * @var array
	 */
	protected $defaults = [
		'connection' => 'default',
		'callback' => NULL,
		'idleTimeout' => 30

		/**
		 * @todo
		 * 
		 * 	exchange?
		 * 	queue?
		 */
	];


	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$consumersConfig = [];

		foreach ($config as $consumerName => $consumerData) {
			$consumersConfig[$consumerName] = $this->extension->validateConfig(
				$this->getDefaults(),
				$consumerData
			);
		}

		$consumersDataBag = $builder->addDefinition($this->extension->prefix('consumersDataBag'))
			->setClass(ConsumersDataBag::class)
			->setArguments([$consumersConfig]);

		return $builder->addDefinition($this->extension->prefix('consumerFactory'))
			->setClass(ConsumerFactory::class)
			->setArguments([$consumersDataBag]);
	}

}
