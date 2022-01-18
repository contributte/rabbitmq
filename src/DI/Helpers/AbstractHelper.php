<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Nette\DI\CompilerExtension;
use Nette\Schema\Schema;

abstract class AbstractHelper
{
	protected CompilerExtension $extension;


	public function __construct(CompilerExtension $extension)
	{
		$this->extension = $extension;
	}


	abstract public function getConfigSchema(): Schema;
}
