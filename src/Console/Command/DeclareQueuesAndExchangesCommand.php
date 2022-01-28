<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Console\Command;

use Contributte\RabbitMQ\Exchange\ExchangeDeclarator;
use Contributte\RabbitMQ\Exchange\ExchangesDataBag;
use Contributte\RabbitMQ\Queue\QueueDeclarator;
use Contributte\RabbitMQ\Queue\QueuesDataBag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeclareQueuesAndExchangesCommand extends Command
{

	public function __construct(
		private QueuesDataBag $queuesDataBag,
		private QueueDeclarator $queueDeclarator,
		private ExchangesDataBag $exchangesDataBag,
		private ExchangeDeclarator $exchangeDeclarator
	) {
		parent::__construct('rabbitmq:declareQueuesAndExchanges');
	}


	protected function configure(): void
	{
		$this->setDescription(
			'Creates all queues and exchanges defined in configs. Intended to run during deploy process'
		);
	}


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('<info>Declaring queues:</info>');

		foreach ($this->queuesDataBag->getDataKeys() as $queueName) {
			$output->writeln($queueName);
			$this->queueDeclarator->declareQueue($queueName);
		}

		$output->writeln('');
		$output->writeln('<info>Declaring exchanges:</info>');

		foreach ($this->exchangesDataBag->getDataKeys() as $exchangeName) {
			$output->writeln($exchangeName);
			$this->exchangeDeclarator->declareExchange($exchangeName);
		}

		$output->writeln('');
		$output->writeln('<info>Declarations done!</info>');

		return 0;
	}
}
