<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Queue;

final class Queue
{

	/**
	 * @var string
	 */
	private $name;

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
	private $exclusive;

	/**
	 * @var bool
	 */
	private $autoDelete;

	/**
	 * @var bool
	 */
	private $noWait;

	/**
	 * @var array
	 */
	private $arguments;


	public function __construct(
		string $name,
		bool $passive,
		bool $durable,
		bool $exclusive,
		bool $autoDelete,
		bool $noWait,
		array $arguments
	) {
		$this->name = $name;
		$this->passive = $passive;
		$this->durable = $durable;
		$this->exclusive = $exclusive;
		$this->autoDelete = $autoDelete;
		$this->noWait = $noWait;
		$this->arguments = $arguments;
	}


	public function getName(): string
	{
		return $this->name;
	}

}
