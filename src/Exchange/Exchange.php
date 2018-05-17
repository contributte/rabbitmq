<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Exchange;

use Gamee\RabbitMQ\Connection\Connection;

final class Exchange
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var bool
	 */
	private $passive;

	/**
	 * @var bool
	 */
	private $durable;

	/**
	 * @var bool
	 */
	private $autoDelete;

	/**
	 * @var bool
	 */
	private $internal;

	/**
	 * @var bool
	 */
	private $noWait;

	/**
	 * @var array
	 */
	private $arguments;

	/**
	 * @var QueueBinding[]
	 */
	private $queueBindings;

	/**
	 * @var Connection
	 */
	private $connection;


	public function __construct(
		string $name,
		string $type,
		bool $passive,
		bool $durable,
		bool $autoDelete,
		bool $internal,
		bool $noWait,
		array $arguments,
		array $queueBindings,
		Connection $connection
	) {
		$this->name = $name;
		$this->type = $type;
		$this->passive = $passive;
		$this->durable = $durable;
		$this->autoDelete = $autoDelete;
		$this->internal = $internal;
		$this->noWait = $noWait;
		$this->arguments = $arguments;
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


	public function getConnection(): Connection
	{
		return $this->connection;
	}
}
