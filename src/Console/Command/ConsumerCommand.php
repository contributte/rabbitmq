<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsumerCommand extends AbstractConsumerCommand
{

	/** @var string */
	protected static $defaultName = 'rabbitmq:consumer';


	protected function configure(): void
	{
		$this->setName(static::$defaultName);
		$this->setDescription('Run a RabbitMQ consumer');

		$this->addArgument('consumerName', InputArgument::REQUIRED, 'Name of the consumer');
		$this->addArgument('secondsToLive', InputArgument::REQUIRED, 'Max seconds for consumer to run');
	}


	/**
	 * @throws \InvalidArgumentException
	 */
	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		$consumerName = (string) $input->getArgument('consumerName');
		$secondsToLive = (int) $input->getArgument('secondsToLive');

		$this->validateConsumerName($consumerName);
		$this->validateSecondsToRun($secondsToLive);

		$consumer = $this->consumerFactory->getConsumer($consumerName);
		$consumer->consume($secondsToLive);
	}


	/**
	 * @throws \InvalidArgumentException
	 */
	private function validateSecondsToRun(int $secondsToLive): void
	{
		if (!$secondsToLive) {
			throw new \InvalidArgumentException(
				'Parameter [secondsToLive] has to be greater then 0'
			);
		}
	}

}
