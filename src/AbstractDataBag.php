<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ;

abstract class AbstractDataBag
{

	/**
	 * @var array<string, mixed>
	 */
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
	 * @param string $key
	 * @return array<string,mixed>
	 */
	public function getDataByKey(string $key): array
	{
		if (!isset($this->data[$key])) {
			throw new \InvalidArgumentException("Data at key [$key] not found");
		}

		return $this->data[$key];
	}

	/**
	 * @return string[]
	 */
	public function getDataKeys(): array
	{
		return array_keys($this->data);
	}
}
