<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Nette\DI\CompilerExtension;

abstract class AbstractHelper
{

	/**
	 * @var array
	 */
	protected array $defaults = [];
	protected CompilerExtension $extension;


	public function __construct(CompilerExtension $extension)
	{
		$this->extension = $extension;
	}


	public function getDefaults(): array
	{
		return $this->defaults;
	}

}
