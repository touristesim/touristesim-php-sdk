<?php

namespace TouristeSIM\Sdk\Auth;

/**
 * Represents an OAuth access token
 */
class Token
{
    private string $accessToken;
    private int $expiresIn;
    private string $tokenType;
    private int $createdAt;

    public function __construct(string $accessToken, int $expiresIn = 3600, string $tokenType = 'Bearer')
    {
        $this->accessToken = $accessToken;
        $this->expiresIn = $expiresIn;
        $this->tokenType = $tokenType;
        $this->createdAt = time();
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): int
    {
        return $this->createdAt + $this->expiresIn;
    }

    /**
     * Check if token is expired (with 60 second buffer)
     */
    public function isExpired(): bool
    {
        return (time() + 60) >= $this->getExpiresAt();
    }

    /**
     * Get time remaining in seconds
     */
    public function getTimeRemaining(): int
    {
        return max(0, $this->getExpiresAt() - time());
    }

    /**
     * Get authorization header value
     */
    public function getAuthorizationHeader(): string
    {
        return "{$this->tokenType} {$this->accessToken}";
    }

    /**
     * Serialize for caching
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * Deserialize from cache
     */
    public static function fromArray(array $data): self
    {
        $token = new self(
            $data['access_token'],
            $data['expires_in'] ?? 3600,
            $data['token_type'] ?? 'Bearer'
        );
        $token->createdAt = $data['created_at'] ?? time();
        return $token;
    }
}
