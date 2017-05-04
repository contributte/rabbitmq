<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Connection;

final class ConnectionFactory
{

	/**
	 * @var ConnectionsDataBag
	 */
	private $connectionsDataBag;

	/**
	 * @var ConnectionFactory
	 */
	private $connectionFactory;


	public function __construct(
		ConnectionsDataBag $connectionsDataBag,
		ConnectionFactory $connectionFactory
	) {
		$this->connectionsDataBag = $connectionsDataBag;
		$this->connectionFactory = $connectionFactory;
	}


	/**
	 * @throws \InvalidArgumentException
	 */
	public function create(string $name): Connection
	{
		throw new \InvalidArgumentException;
	}

}
