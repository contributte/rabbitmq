<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\DI\Helpers;

use Nette\DI\CompilerExtension;
use Nette\Schema\Schema;

abstract class AbstractHelper
{
	public const ACK_TYPES = ['on-confirm', 'on-publish', 'no-ack'];

	public function __construct(
		protected CompilerExtension $extension
	) {
	}


	abstract public function getConfigSchema(): Schema;
}
