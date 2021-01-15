<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Queue;

use Contributte\RabbitMQ\Connection\IConnection;

final class Queue implements IQueue
{

	private string $name;
	private IConnection $connection;

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
	/*private $exclusive;*/

	/**
	 * @var bool
	 */
	/*private $autoDelete;*/

	/**
	 * @var bool
	 */
	/*private $noWait;*/

	/**
	 * @var array
	 */
	/*private $arguments;*/


	public function __construct(
		string $name,
		/*,
		bool $passive,
		bool $durable,
		bool $exclusive,
		bool $autoDelete,
		bool $noWait,
		array $arguments*/
		IConnection $connection
	) {
		$this->name = $name;
		$this->connection = $connection;
		/*$this->passive = $passive;
		$this->durable = $durable;
		$this->exclusive = $exclusive;
		$this->autoDelete = $autoDelete;
		$this->noWait = $noWait;
		$this->arguments = $arguments;*/
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function getConnection(): IConnection
	{
		return $this->connection;
	}
}
