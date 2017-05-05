<?php

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


	public function tearDown()
	{
		$this->bunny->disconnect();
	}

}

$testCase = new ProducerTest;
$testCase->run();
