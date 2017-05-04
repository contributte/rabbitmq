<?php

/**
 * vendor/bin/tester -p /usr/local/Cellar/php70/7.0.11_3/bin/php -c /usr/local/etc/php/7.0/php.ini -c /usr/local/etc/php/7.0/conf.d/ext-redis.ini tests/
 */

namespace Gamee\RabbitMQ\Tests;

require __DIR__ . '/../vendor/autoload.php';

\Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');
