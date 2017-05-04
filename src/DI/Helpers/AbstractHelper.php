<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI\Helpers;

abstract class AbstractHelper extends AbstractHelper
{

	/**
	 * @var array
	 */
	protected $defaults = [];


	public function getDefaults(): array
	{
		return $this->defaults;
	}

}
