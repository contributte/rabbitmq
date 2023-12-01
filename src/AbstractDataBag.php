<?php declare(strict_types = 1);

namespace Contributte\RabbitMQ;

abstract class AbstractDataBag
{

	/** @var array<string, mixed> */
	protected array $data = [];

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct(array $data)
	{
		foreach ($data as $queueOrExchangeName => $config) {
			$this->data[$queueOrExchangeName] = $config;
		}
	}

	/**
	 * @return array<mixed>
	 */
	public function getDataBykey(string $key): array
	{
		if (!isset($this->data[$key])) {
			throw new \InvalidArgumentException(sprintf('Data at key [%s] not found', $key));
		}

		return (array) $this->data[$key];
	}

	/**
	 * @return array<string>
	 */
	public function getDataKeys(): array
	{
		return array_keys($this->data);
	}

}
