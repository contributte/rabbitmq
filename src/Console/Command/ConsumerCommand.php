<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsumerCommand extends AbstractConsumerCommand
{

	protected function configure(): void
	{
		$this->setName('rabbitmq:consumer');
		$this->setDescription('Run a RabbitMQ consumer');

		$this->addArgument('consumerName', InputArgument::REQUIRED, 'Name of the consumer');
		$this->addArgument('secondsToLive', InputArgument::OPTIONAL, 'Max seconds for consumer to run, skip parameter to run indefinitely');
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$consumerName = $input->getArgument('consumerName');
		$secondsToLive = $input->getArgument('secondsToLive');

		if (!is_string($consumerName)) {
			throw new \UnexpectedValueException();
		}

		$this->validateConsumerName($consumerName);

		if ($secondsToLive !== null) {
			if (!is_numeric($secondsToLive)) {
				throw new \UnexpectedValueException();
			}

			$secondsToLive = (int) $secondsToLive;
			$this->validateSecondsToRun($secondsToLive);
		}

		$consumer = $this->consumerFactory->getConsumer($consumerName);
		$consumer->consume($secondsToLive);

		return 0;
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private function validateSecondsToRun(int $secondsToLive): void
	{
		if ($secondsToLive <= 0) {
			throw new \InvalidArgumentException(
				'Parameter [secondsToLive] has to be greater then 0'
			);
		}
	}

}
