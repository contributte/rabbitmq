<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

use Contributte\RabbitMQ\Exchange\IExchange;

class Api implements IApi
{
	private string $url;
	private string $authorization;

	public function __construct(
		string $username,
		string $password,
		bool $secure,
		string $host,
		int $port
	) {
		$this->authorization = $username . ':' . $password;
		$this->url = ($secure ? 'https' : 'http') . '://' . $host . ':' . $port;
	}

	/**
	 * @throws \JsonException
	 * @return array<int, mixed>
	 */
	public function getFederations(): array
	{
		$url = $this->url . '/api/federation-links';
		$response = $this->get($url);

		return (array) $response['data'];
	}

	/**
	 * @throws \JsonException
	 * @return array<int, mixed>
	 */
	public function getPolicies(): array
	{
		$url = $this->url . '/api/policies';
		$response = $this->get($url);

		return (array) $response['data'];
	}

	/**
	 * @throws \JsonException
	 * @param array<string, mixed> $policy
	 */
	public function createFederation(
		string $name,
		string $vhost,
		string $uri,
		int $prefetch,
		int $reconnectDelay,
		?int $messageTTL,
		?int $expires,
		string $ackMode,
		array $policy
	): bool {
		$uniqueName = "{$name}-" . substr(md5($uri), -8);
		$policyName = "{$uniqueName}-policy";
		$federationName = "{$uniqueName}-federation";

		$federationParamsPrototype = [
			'uri' => $uri,
			'prefetch-count' => $prefetch,
			'reconnect-delay' => $reconnectDelay,
			'ack-mode' => $ackMode,
			'exchange' => $name,
		];

		if ($messageTTL) {
			$federationParamsPrototype['message-ttl'] = $messageTTL;
		}
		if ($expires) {
			$federationParamsPrototype['expires'] = $expires;
		}

		$federationParams = [
			'value' => (object) $federationParamsPrototype
		];

		$policyParams = [
			'pattern' => $name,
			'apply-to' => 'exchanges',
			'priority' => $policy['priority'],
			'definition' => (object) ($policy['arguments'] + ['federation-upstream' => $federationName]),
		];

		$this->createFederationUpstream($vhost, $federationName, $federationParams);
		$this->createPolicy($vhost, $policyName, $policyParams);

		return true;
	}

	/**
	 * @param array<string, mixed> $response
	 * @return void
	 */
	private function verifyResponse(array $response): void
	{
		if ($response['status'] < 200 || $response['status'] >= 300) {
			throw new \RuntimeException(
				sprintf(
					'%s: %s',
					$response['data']->error ?? $response['status'],
					$response['data']->reason ?? 'invalid response'
				),
				$response['status']
			);
		}
	}

	/**
	 * @throws \JsonException
	 * @param array<string, mixed> $params
	 * @return array<string, mixed> $params
	 */
	private function put(string $url, array $params): array
	{
		return $this->request('PUT', $url, $params);
	}

	/**
	 * @throws \JsonException
	 * @return array<string, mixed> $params
	 */
	private function get(string $url): array
	{
		return $this->request('GET', $url);
	}

	/**
	 * @param array<string, mixed> $params
	 * @return array<string, mixed>
	 * @throws \JsonException
	 */
	private function request(string $method, string $url, array $params = []): array
	{
		$curl = curl_init($url);
		if ($curl === false) {
			throw new \RuntimeException('Failed to initialize cURL');
		}

		$method = strtoupper($method);

		if ($method === 'PUT') {
			curl_setopt_array($curl, [
				CURLOPT_CUSTOMREQUEST => $method,
				CURLOPT_POSTFIELDS => json_encode($params, JSON_THROW_ON_ERROR),
			]);
		}

		curl_setopt_array($curl, [
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => $this->authorization,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/json'],
			CURLOPT_SSL_VERIFYPEER => true,
		]);

		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);

		return [
			'status' => $info['http_code'],
			'data' => $response ? json_decode((string) $response, flags: JSON_THROW_ON_ERROR) : '',
		];
	}

	/** Policies */

	/**
	 * @param array<string, mixed> $params
	 */
	private function existsPolicy(string $vhost, string $name, array $params): bool
	{
		$policy = $this->getPolicy($vhost, $name);
		$params += ['vhost' => $vhost, 'name' => $name];
		return $policy == $params; // intentionally == as we do not care of order
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getPolicy(string $vhost, string $name): array
	{
		try {
			$url = $this->url . '/api/policies/' . urlencode($vhost) . '/' . $name;
			$response = $this->get($url);

			if ($response['status'] === self::HTTP_NOT_FOUND && isset($response['data']->error)) {
				return [];
			}
		} catch (\JsonException) {
		}

		return (array) ($response['data'] ?? []);
	}


	/**
	 * @throws \JsonException
	 * @param array<string, mixed> $params
	 */
	private function createPolicy(string $vhost, string $name, array $params): void
	{
		if ($this->existsPolicy($vhost, $name, $params)) {
			return;
		}

		$response = $this->put(
			$this->url . '/api/policies/' . urlencode($vhost) . '/' . $name,
			$params
		);

		$this->verifyResponse($response);
	}

	/** Federations */

	/**
	 * @param array<string, mixed> $params
	 */
	private function existsFederationUpstream(string $vhost, string $name, array $params): bool
	{
		$federation = $this->getFederationUpstream($vhost, $name);
		return isset($federation['value']) && $params['value'] == $federation['value']; // intentionally == as keys may be in different order
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getFederationUpstream(string $vhost, string $name): array
	{
		try {
			$url = $this->url . '/api/parameters/federation-upstream/' . urlencode($vhost) . '/' . $name;
			$response = $this->get($url);

			if ($response['status'] === self::HTTP_NOT_FOUND && isset($response['data']->error)) {
				return [];
			}
		} catch (\JsonException) {
		}

		return (array) ($response['data'] ?? []);
	}

	/**
	 * @throws \JsonException
	 * @param array<string, mixed> $params
	 */
	private function createFederationUpstream(string $vhost, string $name, array $params): void
	{
		if ($this->existsFederationUpstream($vhost, $name, $params)) {
			return;
		}

		$response = $this->put(
			$this->url . '/api/parameters/federation-upstream/' . urlencode($vhost) . '/' . $name,
			$params
		);

		$this->verifyResponse($response);
	}
}
