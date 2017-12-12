<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI\Helpers;

use Gamee\RabbitMQ\Queue\QueueFactory;
use Gamee\RabbitMQ\Queue\QueuesDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;

final class QueuesHelper extends AbstractHelper
{

	/**
	 * @var array
	 */
	protected $defaults = [
		'connection' => 'default',
		'passive' => false,
		'durable' => true,
		'exclusive' => false,
		'autoDelete' => false,
		'noWait' => false,
		'arguments' => [],
	];


	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$queuesConfig = [];

		foreach ($config as $queueName => $queueData) {
			$queuesConfig[$queueName] = $this->extension->validateConfig(
				$this->getDefaults(),
				$queueData
			);
		}

		$queuesDataBag = $builder->addDefinition($this->extension->prefix('queuesDataBag'))
			->setFactory(QueuesDataBag::class)
			->setArguments([$queuesConfig]);

		return $builder->addDefinition($this->extension->prefix('queueFactory'))
			->setFactory(QueueFactory::class)
			->setArguments([$queuesDataBag]);
	}

}
