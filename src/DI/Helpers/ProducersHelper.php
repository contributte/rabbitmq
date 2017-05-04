<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\DI\Helpers;

use Gamee\RabbitMQ\Producer;

final class ProducersHelper extends AbstractHelper
{

	/**
	 * @var array
	 */
	protected $defaults = [
		'connection' => 'default'
		'exchange' => [],
		'queue' => [],
		'contentType' => 'text/plain',
		'deliveryMode' => Producer::DELIVERY_MODE_PERSISTENT
	];


	public function setupProducers(ContainerBuilder $builder, array $config): void
	{
		if (empty($config)) {
			return;
		}

		$config = $this->validateConfig($this->getDefaults(), $config);

		dump($config); die;
	}

}
