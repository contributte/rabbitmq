<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Producer;

final class Producer
{

	const DELIVERY_MODE_NON_PERSISTENT = 1;
	const DELIVERY_MODE_PERSISTENT = 2;


	public function __construct()
	{
		
	}


	public function publish(): void
	{
		// Code here
	}

}
