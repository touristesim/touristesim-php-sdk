<?php

namespace TouristeSIM\Sdk\Exceptions;

use Exception;

/**
 * Base exception for all Tourist eSIM SDK errors
 */
class ApiException extends Exception
{
    protected int $statusCode;
    protected ?array $response = null;
    protected ?string $requestId = null;

    public function __construct(
        string $message,
        int $statusCode = 0,
        ?array $response = null,
        ?string $requestId = null,
        Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->response = $response;
        $this->requestId = $requestId;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }
}

/**
 * Thrown when authentication fails
 */
class AuthenticationException extends ApiException
{
    public static function invalidCredentials(): self
    {
        return new self('Invalid client credentials. Check your clientId and clientSecret.');
    }

    public static function tokenExpired(): self
    {
        return new self('Access token has expired.');
    }

    public static function unauthorized(): self
    {
        return new self('Unauthorized. Check your API credentials.');
    }
}

/**
 * Thrown when request validation fails
 */
class ValidationException extends ApiException
{
    public static function fromResponse(array $errors): self
    {
        $message = 'Validation failed: ' . implode(', ', array_values($errors)[0] ?? []);
        return new self($message);
    }
}

/**
 * Thrown when API rate limit is exceeded
 */
class RateLimitException extends ApiException
{
    private int $retryAfter = 60;

    public function __construct(int $retryAfter = 60, Exception $previous = null)
    {
        parent::__construct('Rate limit exceeded. Please try again later.', 429, null, null, $previous);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}

/**
 * Thrown when a resource is not found
 */
class ResourceNotFoundException extends ApiException
{
    public static function plan(int $planId): self
    {
        return new self("Plan with ID {$planId} not found.", 404);
    }

    public static function order(int $orderId): self
    {
        return new self("Order with ID {$orderId} not found.", 404);
    }

    public static function esim(string $iccid): self
    {
        return new self("eSIM with ICCID {$iccid} not found.", 404);
    }
}

/**
 * Thrown when API returns a server error
 */
class ServerException extends ApiException
{
    public static function maintenance(): self
    {
        return new self('API is under maintenance. Please try again later.', 503);
    }
}

/**
 * Thrown when there's a network/connection error
 */
class ConnectionException extends ApiException
{
    public static function timeout(): self
    {
        return new self('Request timeout. The API did not respond in time.');
    }

    public static function connectionFailed(string $reason): self
    {
        return new self("Connection failed: {$reason}");
    }
}
