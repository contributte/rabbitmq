<?php

declare(strict_types=1);

namespace Gamee\RabbitMQ\Console\Command;

use Gamee\RabbitMQ\Exchange\ExchangeFactory;
use Gamee\RabbitMQ\Exchange\ExchangesDataBag;
use Gamee\RabbitMQ\Queue\QueueFactory;
use Gamee\RabbitMQ\Queue\QueuesDataBag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeclareQueuesAndExchangesCommand extends Command
{

	private const COMMAND_NAME = 'rabbitmq:declareQueuesAndExchanges';

	/**
	 * @var QueuesDataBag
	 */
	private $queuesDataBag;

	/**
	 * @var ExchangesDataBag
	 */
	private $exchangesDataBag;

	/**
	 * @var QueueFactory
	 */
	private $queueFactory;

	/**
	 * @var ExchangeFactory
	 */
	private $exchangeFactory;


	public function __construct(
		QueuesDataBag $queuesDataBag,
		QueueFactory $queueFactory,
		ExchangesDataBag $exchangesDataBag,
		ExchangeFactory $exchangeFactory
	)
	{
		parent::__construct(self::COMMAND_NAME);
		$this->queuesDataBag = $queuesDataBag;
		$this->exchangesDataBag = $exchangesDataBag;
		$this->queueFactory = $queueFactory;
		$this->exchangeFactory = $exchangeFactory;
	}


	protected function configure(): void
	{
		$this->setDescription(
			'Creates all queues and exchanges defined in configs. Intended to run during deploy process'
		);
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Declaring queues:</info>');
		foreach ($this->queuesDataBag->getDataKeys() as $queueName) {
			$output->writeln($queueName);
			$this->queueFactory->getQueue($queueName, true);
		}

		$output->writeln('');

		$output->writeln('<info>Declaring exchanges:</info>');
		foreach ($this->exchangesDataBag->getDataKeys() as $exchangeName) {
			$output->writeln($exchangeName);
			$this->exchangeFactory->getExchange($exchangeName, true);
		}

		$output->writeln('');
		$output->writeln('<info>Declarations done!</info>');
	}

}
