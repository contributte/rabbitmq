<?php

declare(strict_types=1);

namespace Contributte\RabbitMQ\Connection;

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

	public function getFederations(): array
	{
		$url = $this->url . '/api/federation-links';
		$response = $this->get($url);

		return (array) $response['data'];
	}

	public function createFederation(
		string $exchange,
		string $vhost,
		string $uri,
		int $prefetch,
		int $reconnectDelay,
		int $messageTTL,
		int $expires,
		string $ackMode
	): bool {
		$uniqueName = $exchange . '-' . substr(md5($uri), -8);
		$policyName = $uniqueName . '-policy';
		$federationName = $uniqueName . '-federation';

		$federationParams = [
			'value' => (object) [
				'uri' => $uri,
				'prefetch-count' => $prefetch,
				'reconnect-delay' => $reconnectDelay,
				'message-ttl' => $messageTTL * 1000,
				'expires' => $expires * 1000,
				'ack-mode' => $ackMode,
				'exchange' => $exchange,
			],
		];
		$policyParams = [
			'pattern' => $exchange,
			'apply-to' => 'exchanges',
			'definition' => (object) [
				'federation-upstream' => $federationName,
				'message-ttl' => $messageTTL * 1000,
			],
		];

		$this->createFederationUpstream($federationName, $vhost, $federationParams);
		$this->createFederationPolicy($policyName, $vhost, $policyParams);

		return true;
	}

	private function createFederationUpstream(string $name, string $vhost, array $params): void
	{
		$response = $this->put(
			$this->url . '/api/parameters/federation-upstream/' . urlencode($vhost) . '/' . $name,
			$params
		);

		$this->verifyResponse($response);
	}

	private function createFederationPolicy(string $name, string $vhost, array $params): void
	{
		$response = $this->put(
			$this->url . '/api/policies/' . urlencode($vhost) . '/' . $name,
			$params
		);

		$this->verifyResponse($response);
	}

	private function verifyResponse(array $response): void
	{
		if ($response['status'] <= 200 || $response['status'] >= 300) {
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

	private function put(string $url, array $params): array
	{
		return $this->request('PUT', $url, $params);
	}

	private function get(string $url): array
	{
		return $this->request('GET', $url);
	}

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
				CURLOPT_POSTFIELDS => json_encode($params),
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
			'data' => json_decode((string) $response),
		];
	}
}
