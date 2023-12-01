<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ\Producer;

use Contributte\RabbitMQ\AbstractDataBag;
use Contributte\RabbitMQ\DI\Helpers\ProducersHelper;

final class ProducersDataBag extends AbstractDataBag
{

	/**
	 * @param array<string, mixed> $data
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $data)
	{
		parent::__construct($data);

		foreach ($data as $producerName => $producer) {
			$this->addProducerByData($producerName, $producer);
		}
	}

	/**
	 * @param array<string, mixed> $data
	 * @throws \InvalidArgumentException
	 */
	public function addProducerByData(string $producerName, array $data): void
	{
		$data['deliveryMode'] ??= Producer::DELIVERY_MODE_PERSISTENT;
		$data['contentType'] ??= 'text/plain';
		$data['exchange'] ??= null;
		$data['queue'] ??= null;

		if (!in_array($data['deliveryMode'], ProducersHelper::DELIVERY_MODES, true)) {
			throw new \InvalidArgumentException(
				sprintf('Unknown exchange type [%s]', $data['type'])
			);
		}

		/**
		 * 1, Producer has to be subscribed to either a queue or an exchange
		 * 2, A producer can be subscribed to both a queue and an exchange
		 */
		if ($data['queue'] === [] && $data['exchange'] === []) {
			throw new \InvalidArgumentException(
				'Producer has to be subscribed to either a queue or an exchange'
			);
		}

		$this->data[$producerName] = $data;
	}

}
