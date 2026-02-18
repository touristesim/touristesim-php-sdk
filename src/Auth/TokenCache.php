<?php

namespace TouristeSIM\Sdk\Auth;

/**
 * Simple file-based token cache
 */
class TokenCache
{
    private string $cacheDir;

    public function __construct(?string $cacheDir = null)
    {
        if ($cacheDir) {
            $this->cacheDir = $cacheDir;
        } else {
            // Use default temp directory
            $this->cacheDir = sys_get_temp_dir();
        }

        // Ensure cache directory exists
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get cached token
     */
    public function get(string $key): ?Token
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return null;
        }

        try {
            $data = json_decode(file_get_contents($filePath), true);
            return Token::fromArray($data);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Store token in cache
     */
    public function store(string $key, Token $token): void
    {
        $filePath = $this->getFilePath($key);

        try {
            file_put_contents(
                $filePath,
                json_encode($token->toArray()),
                LOCK_EX
            );
            chmod($filePath, 0600); // Secure: readable/writable by owner only
        } catch (\Throwable) {
            // Silently fail - caching is optional
        }
    }

    /**
     * Remove cached token
     */
    public function forget(string $key): void
    {
        $filePath = $this->getFilePath($key);

        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    /**
     * Clear all cached tokens
     */
    public function flush(): void
    {
        $pattern = $this->cacheDir . '/tourist_esim_token_*.json';
        foreach (glob($pattern) as $file) {
            @unlink($file);
        }
    }

    /**
     * Get full file path for cache key
     */
    private function getFilePath(string $key): string
    {
        return $this->cacheDir . '/' . $key . '.json';
    }
}
