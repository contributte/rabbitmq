<?php

declare(strict_types=1);

namespace Gamee\RabbitMQ\Tests\Cases;

use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php'; 

final class ProducerTest extends TestCase
{

	public function setUp()
	{
		
	}


	public function testFoo()
	{
		Assert::true(true);
	}
}

$testCase = new ProducerTest;
$testCase->run();
