<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI\Helpers;

use Gamee\RabbitMQ\DI\RabbitMQExtension;

abstract class AbstractHelper
{

	/**
	 * @var array
	 */
	protected $defaults = [];

	/**
	 * @var RabbitMQExtension
	 */
	protected $extension;


	public function __construct(RabbitMQExtension $extension)
	{
		$this->extension = $extension;
	}


	public function getDefaults(): array
	{
		return $this->defaults;
	}

}
