<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Console\Command;

use Gamee\RabbitMQ\Consumer\ConsumerFactory;
use Gamee\RabbitMQ\Consumer\ConsumersDataBag;
use Gamee\RabbitMQ\Consumer\Exception\ConsumerFactoryException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsumerCommand extends Command
{

	const COMMAND_NAME = 'rabbitmq:consumer';

	/**
	 * @var ConsumersDataBag
	 */
	private $consumersDataBag;

	/**
	 * @var ConsumerFactory
	 */
	private $consumerFactory;


	public function __construct(ConsumersDataBag $consumersDataBag, ConsumerFactory $consumerFactory)
	{
		parent::__construct();

		$this->consumersDataBag = $consumersDataBag;
		$this->consumerFactory = $consumerFactory;
	}


	protected function configure(): void
	{
		$this->setName(self::COMMAND_NAME);
		$this->setDescription('Run a RabbitMQ consumer');

		$this->addArgument('consumerName', InputArgument::REQUIRED, 'The name of the consumer');
		$this->addArgument('secondsToLive', InputArgument::REQUIRED, 'Max seconds for consumer to run');
	}


	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		$consumerName = (string) $input->getArgument('consumerName');
		$secondsToLive = (int) $input->getArgument('secondsToLive');

		$this->validateInput($consumerName, $secondsToLive, $output); // May exit;

		$consumer = $this->consumerFactory->getConsumer($consumerName);
		$consumer->consumeForSpecifiedTime($secondsToLive);
	}


	private function validateInput(string $consumerName, int $secondsToLive): void
	{
		if (!$secondsToLive) {
			throw new \InvalidArgumentException(
				'Parameter [secondsToLive] has to be greater then 0'
			);
		}

		/**
		 * Validate consumer name
		 */
		try {
			$this->consumerFactory->getConsumer($consumerName);

		} catch (ConsumerFactoryException $e) {
			throw new \InvalidArgumentException(
				sprintf(
					"Consumer [$consumerName] does not exist. \n\n Available consumers: %s",
					implode('', array_map(function($s) {
						return "\n\t- [{$s}]";
					}, $this->consumersDataBag->getDatakeys()))
				)
			);
		}
	}

}
