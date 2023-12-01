<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Contributte\RabbitMQ\Producer\Producer;
use Contributte\RabbitMQ\Producer\ProducerFactory;
use Contributte\RabbitMQ\Producer\ProducersDataBag;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;

final class ProducersHelper extends AbstractHelper
{

	public const DELIVERY_MODES = [
		Producer::DELIVERY_MODE_NON_PERSISTENT,
		Producer::DELIVERY_MODE_PERSISTENT,
	];

	/** @var array<string, mixed> */
	protected array $defaults = [
		'exchange' => null,
		'queue' => null,
		'contentType' => 'text/plain',
		'deliveryMode' => Producer::DELIVERY_MODE_PERSISTENT,
	];

	/**
	 * @param array<string, mixed> $config
	 */
	public function setup(ContainerBuilder $builder, array $config = []): ServiceDefinition
	{
		$producersConfig = [];

		foreach ($config as $producerName => $producerData) {
			// @phpstan-ignore-next-line
			$producerConfig = $this->extension->validateConfig(
				$this->getDefaults(),
				$producerData
			);

			$producersConfig[$producerName] = $producerConfig;
		}

		$producersDataBag = $builder->addDefinition($this->extension->prefix('producersDataBag'))
			->setFactory(ProducersDataBag::class)
			->setArguments([$producersConfig]);

		return $builder->addDefinition($this->extension->prefix('producerFactory'))
			->setFactory(ProducerFactory::class)
			->setArguments([$producersDataBag]);
	}

}
