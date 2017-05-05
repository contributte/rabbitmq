<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Console\Command;

use Gamee\RabbitMQ\Consumer\ConsumersDataBag;
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


	public function __construct(ConsumersDataBag $consumersDataBag)
	{
		$this->consumersDataBag = $consumersDataBag;

		parent::__construct();
	}


	protected function configure(): void
	{
		$this->setName(self::COMMAND_NAME);
		$this->setDescription('Run a RabbitMQ consumer');

		$this->addArgument('consumerName', InputArgument::REQUIRED, 'The name of the consumer');
	}


	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		$consumerName = $input->getArgument('consumerName');

		/**
		 * Validate consumer name
		 */
		if (!isset($this->consumersDataBag->getDatakeys()[$consumerName])) {
			$this->writeError(
				$output,
				sprintf(
					"Consumer [$consumerName] does not exist. \n\n Available consumers: %s",
					implode('', array_map(function($s) {
						return "\n\t- [{$s}]";
					}, $this->consumersDataBag->getDatakeys()))
				)
			);
		}
	}


	private function writeError(OutputInterface $output, string $message): void
	{
		$output->writeln("<error>\n\n {$message}\n</error>\n");
	}

}
