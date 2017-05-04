<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI\Helpers;

final class ConnectionsHelper
{

	/**
	 * @var array
	 */
	protected $defaults = [
		'host': '127.0.0.1',
		'port': '5672',
		'user': 'guest',
		'password': 'guest',
		'vhost': '/'
	];

}
