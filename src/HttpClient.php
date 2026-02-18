<?php

namespace TouristeSIM\Sdk;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use TouristeSIM\Sdk\Auth\OAuthClient;
use TouristeSIM\Sdk\Exceptions\{
    ApiException,
    AuthenticationException,
    RateLimitException,
    ResourceNotFoundException,
    ServerException,
    ValidationException,
    ConnectionException
};

/**
 * HTTP Client with OAuth, retry logic, and error handling
 */
class HttpClient
{
    private Config $config;
    private OAuthClient $oauth;
    private GuzzleClient $client;
    private int $maxRetries = 3;
    private int $retryDelayMs = 100;

    public function __construct(Config $config, OAuthClient $oauth)
    {
        $this->config = $config;
        $this->oauth = $oauth;
        $this->client = new GuzzleClient($config->getHttpClientOptions());
    }

    /**
     * Make GET request
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, [
            'query' => $query,
        ]);
    }

    /**
     * Make POST request
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Make PUT request
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Make DELETE request
     */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Execute request with retry logic and error handling
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        $options = array_merge($options, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->oauth->getToken(),
            ],
        ]);

        // Retry logic
        $lastException = null;
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = $this->client->request($method, $endpoint, $options);
                return $this->parseResponse($response);
            } catch (GuzzleException $e) {
                $lastException = $e;

                // Check if we should retry
                if ($this->shouldRetry($e, $attempt)) {
                    $delay = $this->retryDelayMs * $attempt;
                    usleep($delay * 1000); // Convert ms to microseconds
                    continue;
                }

                // Don't retry - throw error
                throw $this->handleException($e);
            }
        }

        throw $this->handleException($lastException ?? new \Exception('Request failed'));
    }

    /**
     * Determine if request should be retried
     */
    private function shouldRetry(GuzzleException $e, int $attempt): bool
    {
        if ($attempt >= $this->maxRetries) {
            return false;
        }

        // Retry on connection errors
        if ($e instanceof ConnectException) {
            return true;
        }

        // Retry on server errors (5xx)
        if ($e instanceof RequestException && $e->getResponse()) {
            $statusCode = $e->getResponse()->getStatusCode();
            return $statusCode >= 500;
        }

        return false;
    }

    /**
     * Parse successful response
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $contents = $response->getBody()->getContents();

        try {
            return json_decode($contents, true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (\JsonException) {
            return ['raw_response' => $contents];
        }
    }

    /**
     * Convert Guzzle exception to SDK exception
     */
    private function handleException(GuzzleException|\Throwable $e): ApiException
    {
        if ($e instanceof RequestException && $e->getResponse()) {
            return $this->handleResponseException($e);
        }

        if ($e instanceof ConnectException) {
            return ConnectionException::connectionFailed($e->getMessage());
        }

        if ($e instanceof GuzzleException) {
            return ConnectionException::connectionFailed($e->getMessage());
        }

        return new ApiException($e->getMessage());
    }

    /**
     * Handle HTTP response exceptions
     */
    private function handleResponseException(RequestException $e): ApiException
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $requestId = $response->getHeader('X-Request-ID')[0] ?? null;

        try {
            $data = json_decode($response->getBody()->getContents(), true);
        } catch (\Throwable) {
            $data = [];
        }

        match ($statusCode) {
            401 => throw AuthenticationException::unauthorized(),
            404 => throw new ResourceNotFoundException($e->getMessage(), 404, $data, $requestId),
            422 => throw ValidationException::fromResponse($data['errors'] ?? []),
            429 => throw new RateLimitException(60, $e),
            503 => throw ServerException::maintenance(),
            default => throw new ApiException(
                $data['message'] ?? $e->getMessage(),
                $statusCode,
                $data,
                $requestId,
                $e
            ),
        };
    }

    /**
     * Set maximum retry attempts
     */
    public function setMaxRetries(int $maxRetries): self
    {
        $this->maxRetries = max(1, $maxRetries);
        return $this;
    }

    /**
     * Set retry delay in milliseconds
     */
    public function setRetryDelay(int $delayMs): self
    {
        $this->retryDelayMs = max(0, $delayMs);
        return $this;
    }
}
