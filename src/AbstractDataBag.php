<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ;

abstract class AbstractDataBag
{

	/**
	 * @var array
	 */
	protected $data = [];


	public function __construct(array $data)
	{
		$this->data = $data;
	}


	public function getDataBykey(string $key): array
	{
		if (!isset($this->data[$key])) {
			throw new \InvalidArgumentException("Data at key [$key] not found");
		}

		return (array) $this->data[$key];
	}


	public function getDatakeys(): array
	{
		return array_keys($this->data);
	}

}
