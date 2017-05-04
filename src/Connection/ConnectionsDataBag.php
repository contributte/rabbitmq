<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Connection;

final class ConnectionsDataBag
{

	/**
	 * @var array
	 */
	private $connectionsData = [];


	public function __construct(array $connectionsData)
	{
		$this->connectionsData = $connectionsData;
	}

}
