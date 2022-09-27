<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection\Exception;

use Bunny\Exception\ClientException;

class WaitTimeoutException extends ClientException
{
}
