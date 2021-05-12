<?php

declare(strict_types = 1);

namespace Contributte\RabbitMQ\Tests\Cases\DI;

use Contributte\RabbitMQ\Connection\Connection;
use Contributte\RabbitMQ\Connection\ConnectionFactory;
use Contributte\RabbitMQ\DI\RabbitMQExtension24;
use Contributte\RabbitMQ\Tests\Toolkit\NeonLoader;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class RabbitMQExtensionTest extends TestCase
{

	public function testDefault(): void
	{
		$loader = new ContainerLoader(TMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('rabbitmq', new RabbitMQExtension24());
			$compiler->addConfig(NeonLoader::load('
			rabbitmq:
				connections:
					default:
						user: guest
						password: guest
						host: localhost
						port: 5672
						lazy: false
			'));
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		/** @var Container $container */
		$container = new $class();

		Assert::type(ConnectionFactory::class, $container->getByType(ConnectionFactory::class));
	}

}

(new RabbitMQExtensionTest())->run();
