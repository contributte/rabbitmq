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

final class StaticConsumerCommand extends AbstractConsumerCommand
{

	/**
	 * @var string
	 */
	protected static $defaultName = 'rabbitmq:staticConsumer';


	protected function configure(): void
	{
		$this->setName(self::$defaultName);
		$this->setDescription('Run a RabbitMQ consumer but consume just particular amount of messages');

		$this->addArgument('consumerName', InputArgument::REQUIRED, 'Name of the consumer');
		$this->addArgument('amountOfMessages', InputArgument::REQUIRED, 'Amount of messages to consume');
	}


	/**
	 * @throws \InvalidArgumentException
	 */
	protected function execute(InputInterface $input, OutputInterface $output): ?int
	{
		$consumerName = $input->getArgument('consumerName');
		$amountOfMessages = $input->getArgument('amountOfMessages');

		if (!is_string($consumerName)) {
			throw new \UnexpectedValueException;
		}

		if (!is_numeric($amountOfMessages)) {
			throw new \UnexpectedValueException;
		}

		$amountOfMessages = (int) $amountOfMessages;

		$this->validateConsumerName($consumerName);
		$this->validateAmountOfMessages($amountOfMessages);

		$consumer = $this->consumerFactory->getConsumer($consumerName);
		$consumer->consume(null, $amountOfMessages);

		return 0;
	}


	/**
	 * @throws \InvalidArgumentException
	 */
	private function validateAmountOfMessages(int $amountOfMessages): void
	{
		if ($amountOfMessages <= 0) {
			throw new \InvalidArgumentException(
				'Parameter [amountOfMessages] has to be greater then 0'
			);
		}
	}
}
