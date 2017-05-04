<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Producer;

final class ProducersDataBag
{

	/**
	 * @var array
	 */
	private $producersData = [];


	public function __construct(array $producersData)
	{
		$this->producersData = $producersData;
	}

}
