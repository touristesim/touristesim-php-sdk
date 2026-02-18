<?php

namespace TouristeSIM\Sdk\Auth;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use TouristEsim\Sdk\Config;
use TouristEsim\Sdk\Exceptions\AuthenticationException;
use TouristEsim\Sdk\Exceptions\ConnectionException;

/**
 * OAuth 2.0 Client for Tourist eSIM Partner API
 * Handles token acquisition and refresh automatically
 */
class OAuthClient
{
    private Config $config;
    private HttpClient $httpClient;
    private ?Token $token = null;
    private TokenCache $cache;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->cache = new TokenCache();
        
        $options = $config->getHttpClientOptions();
        unset($options['headers']); // Remove custom headers for token endpoint
        $this->httpClient = new HttpClient($options);
    }

    /**
     * Get a valid access token, refreshing if needed
     */
    public function getToken(): string
    {
        $token = $this->getValidToken();
        return $token->getAccessToken();
    }

    /**
     * Get Token object with all metadata
     */
    public function getValidToken(): Token
    {
        // Check if we have a valid cached token
        if ($this->token && !$this->token->isExpired()) {
            return $this->token;
        }

        // Try to load from cache
        $cachedToken = $this->cache->get($this->getCacheKey());
        if ($cachedToken && !$cachedToken->isExpired()) {
            $this->token = $cachedToken;
            return $this->token;
        }

        // Request new token from API
        $this->token = $this->requestToken();
        $this->cache->store($this->getCacheKey(), $this->token);

        return $this->token;
    }

    /**
     * Request new token from OAuth endpoint
     */
    private function requestToken(): Token
    {
        try {
            $response = $this->httpClient->post($this->config->getOAuthTokenUrl(), [
                'json' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->config->getClientId(),
                    'client_secret' => $this->config->getClientSecret(),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['access_token'])) {
                throw AuthenticationException::invalidCredentials();
            }

            return new Token(
                $data['access_token'],
                $data['expires_in'] ?? 3600,
                $data['token_type'] ?? 'Bearer'
            );
        } catch (GuzzleException $e) {
            if ($e->getCode() === 401) {
                throw AuthenticationException::invalidCredentials();
            }

            throw ConnectionException::connectionFailed($e->getMessage());
        }
    }

    /**
     * Revoke current token
     */
    public function revokeToken(): bool
    {
        if (!$this->token) {
            return false;
        }

        try {
            $this->httpClient->post($this->config->getBaseUrl() . '/../oauth/revoke', [
                'json' => [
                    'token' => $this->token->getAccessToken(),
                ],
                'headers' => [
                    'Authorization' => "Bearer {$this->token->getAccessToken()}",
                ],
            ]);

            $this->cache->forget($this->getCacheKey());
            $this->token = null;
            return true;
        } catch (GuzzleException) {
            return false;
        }
    }

    /**
     * Get cache key for storing tokens
     */
    private function getCacheKey(): string
    {
        return 'tourist_esim_token_' . md5($this->config->getClientId());
    }
}
