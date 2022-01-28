<?php declare(strict_types = 1);

use Ninjify\Nunjuck\Environment;

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}

// Bypass finals
\Tester\Environment::bypassFinals();

// Configure environment
Environment::setupTester();
Environment::setupTimezone();
Environment::setupVariables(__DIR__);
