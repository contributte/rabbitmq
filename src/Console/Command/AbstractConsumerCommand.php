<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Console\Command;

use Contributte\RabbitMQ\Consumer\ConsumerFactory;
use Contributte\RabbitMQ\Consumer\ConsumersDataBag;
use Contributte\RabbitMQ\Consumer\Exception\ConsumerFactoryException;
use Symfony\Component\Console\Command\Command;

abstract class AbstractConsumerCommand extends Command
{

	protected ConsumersDataBag $consumersDataBag;
	protected ConsumerFactory $consumerFactory;


	public function __construct(ConsumersDataBag $consumersDataBag, ConsumerFactory $consumerFactory)
	{
		parent::__construct();

		$this->consumersDataBag = $consumersDataBag;
		$this->consumerFactory = $consumerFactory;
	}


	/**
	 * @throws \InvalidArgumentException
	 */
	protected function validateConsumerName(string $consumerName): void
	{
		try {
			$this->consumerFactory->getConsumer($consumerName);

		} catch (ConsumerFactoryException $e) {
			throw new \InvalidArgumentException(
				$e->getMessage() . sprintf(
					"\n\n Available consumers: %s",
					implode('', array_map(static function($s): string {
						return "\n\t- [{$s}]";
					}, $this->consumersDataBag->getDataKeys()))
				)
			);
		}
	}

}
