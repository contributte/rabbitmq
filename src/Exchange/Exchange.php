<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Exchange;

use Contributte\RabbitMQ\Connection\IConnection;

final class Exchange implements IExchange
{

	private string $name;

	/**
	 * @var string
	 */
	/*private $type;*/

	/**
	 * @var bool
	 */
	/*private $passive;*/

	/**
	 * @var bool
	 */
	/*private $durable;*/

	/**
	 * @var bool
	 */
	/*private $autoDelete;*/

	/**
	 * @var bool
	 */
	/*private $internal;*/

	/**
	 * @var bool
	 */
	/*private $noWait;*/

	/**
	 * @var array
	 */
	/*private $arguments;*/

	/**
	 * @var QueueBinding[]
	 */
	private array $queueBindings;
	private IConnection $connection;


	public function __construct(
		string $name,
		/*string $type,
		bool $passive,
		bool $durable,
		bool $autoDelete,
		bool $internal,
		bool $noWait,
		array $arguments,*/
		array $queueBindings,
		IConnection $connection
	) {
		$this->name = $name;
		/*$this->type = $type;
		$this->passive = $passive;
		$this->durable = $durable;
		$this->autoDelete = $autoDelete;
		$this->internal = $internal;
		$this->noWait = $noWait;
		$this->arguments = $arguments;*/
		$this->queueBindings = $queueBindings;
		$this->connection = $connection;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function getQueueBindings(): array
	{
		return $this->queueBindings;
	}


	public function getConnection(): IConnection
	{
		return $this->connection;
	}
}
