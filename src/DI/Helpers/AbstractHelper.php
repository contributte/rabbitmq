<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Contributte\RabbitMQ\DI\RabbitMQExtension;

abstract class AbstractHelper
{

	/**
	 * @var array
	 */
	protected array $defaults = [];

	protected RabbitMQExtension $extension;


	public function __construct(RabbitMQExtension $extension)
	{
		$this->extension = $extension;
	}


	public function getDefaults(): array
	{
		return $this->defaults;
	}

}
