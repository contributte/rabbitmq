<?php

declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2017 gameeapp.com <hello@gameeapp.com>
 * @author      Pavel Janda <pavel@gameeapp.com>
 * @package     Gamee
 */

namespace Gamee\RabbitMQ\Producer;

use Gamee\RabbitMQ\AbstractDataBag;
use Gamee\RabbitMQ\DI\Helpers\ProducersHelper;

final class ProducersDataBag extends AbstractDataBag
{

	/**
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $data)
	{
		foreach ($data as $producerName => $producer) {
			$this->addProducerByData($producerName, $producer);
		}
	}


	/**
	 * @throws \InvalidArgumentException
	 */
	public function addProducerByData(string $producerName, array $data): void
	{
		$data['deliveryMode'] = $data['deliveryMode'] ?? Producer::DELIVERY_MODE_PERSISTENT;
		$data['contentType'] = $data['contentType'] ?? 'text/plain';
		$data['exchange'] = $data['exchange'] ?? null;
		$data['queue'] = $data['queue'] ?? null;

		if (!in_array($data['deliveryMode'], ProducersHelper::DELIVERY_MODES, true)) {
			throw new \InvalidArgumentException(
				"Unknown exchange type [{$data['type']}]"
			);
		}

		/**
		 * 1, Producer has to be subscribed to either a queue or an exchange
		 * 2, A producer can be subscribed to both a queue and an exchange
		 */
		if (empty($data['queue']) && empty($data['exchange'])) {
			throw new \InvalidArgumentException(
				'Producer has to be subscribed to either a queue or an exchange'
			);
		}

		$this->data[$producerName] = $data;
	}
}
