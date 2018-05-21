<?php

declare(strict_types=1);

namespace Gamee\RabbitMQ\Tests\Cases;

use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php'; 

final class FooTest extends TestCase
{

	public function testBasicFuncionality(): void
	{
		Assert::true(true);
	}
}

(new FooTest)->run();
