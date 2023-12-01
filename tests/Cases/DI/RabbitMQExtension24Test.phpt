<?php declare(strict_types = 1);

namespace Tests\Cases\DI;

use Contributte\RabbitMQ\Connection\ConnectionFactory;
use Contributte\RabbitMQ\DI\RabbitMQExtension24;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Nette\DI\Compiler;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class RabbitMQExtension24Test extends TestCase
{

	public function testDefault(): void
	{
		$container = ContainerBuilder::of()
			->withCompiler(function (Compiler $compiler): void {
				$compiler->addExtension('rabbitmq', new RabbitMQExtension24());
				$compiler->addConfig(Neonkit::load('
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
			})
			->build();

		Assert::type(ConnectionFactory::class, $container->getByType(ConnectionFactory::class));
	}

}

(new RabbitMQExtension24Test())->run();
