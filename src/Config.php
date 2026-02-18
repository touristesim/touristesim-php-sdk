<?php

namespace TouristeSIM\Sdk;

/**
 * Configuration class for TouristeSIM Partner SDK
 */
class Config
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl = 'https://api.touristesim.net/v1';
    private string $mode = 'sandbox'; // 'sandbox' or 'production'
    private bool $verifySSL = true;
    private int $timeout = 30;
    private int $connectTimeout = 10;
    private ?string $userAgent = null;

    public function __construct(string $clientId, string $clientSecret, array $options = [])
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        if (isset($options['base_url'])) {
            $this->baseUrl = rtrim($options['base_url'], '/');
        }

        if (isset($options['mode']) && in_array($options['mode'], ['sandbox', 'production'])) {
            $this->mode = $options['mode'];
        }

        if (isset($options['verify_ssl'])) {
            $this->verifySSL = (bool)$options['verify_ssl'];
        }

        if (isset($options['timeout'])) {
            $this->timeout = (int)$options['timeout'];
        }

        if (isset($options['connect_timeout'])) {
            $this->connectTimeout = (int)$options['connect_timeout'];
        }

        if (isset($options['user_agent'])) {
            $this->userAgent = $options['user_agent'];
        } else {
            $this->userAgent = $this->getDefaultUserAgent();
        }
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function isSandbox(): bool
    {
        return $this->mode === 'sandbox';
    }

    public function isProduction(): bool
    {
        return $this->mode === 'production';
    }

    public function shouldVerifySSL(): bool
    {
        return $this->verifySSL;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function getOAuthTokenUrl(): string
    {
        return $this->baseUrl . '/../oauth/token';
    }

    private function getDefaultUserAgent(): string
    {
        $phpVersion = phpversion();
        $curlVersion = curl_version()['version'] ?? 'unknown';
        return "TouristeSIM-SDK/1.0.0 (PHP/{$phpVersion}; cURL/{$curlVersion})";
    }

    /**
     * Get HTTP client options for Guzzle
     */
    public function getHttpClientOptions(): array
    {
        return [
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectTimeout,
            'verify' => $this->verifySSL,
            'headers' => [
                'User-Agent' => $this->getUserAgent(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];
    }
}
