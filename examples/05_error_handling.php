<?php
/**
 * Example 5: Error Handling
 * 
 * Demonstrates how to handle different types of errors and exceptions
 * thrown by the SDK.
 */

require __DIR__ . '/../vendor/autoload.php';

use TouristeSIM\Sdk\TouristEsim;
use TouristeSIM\Sdk\Exceptions\{
    ApiException,
    AuthenticationException,
    ValidationException,
    RateLimitException,
    ResourceNotFoundException,
    ServerException,
    ConnectionException
};

$sdk = new TouristEsim(
    clientId: getenv('TOURIST_ESIM_CLIENT_ID') ?: 'invalid-client-id',
    clientSecret: getenv('TOURIST_ESIM_CLIENT_SECRET') ?: 'invalid-secret'
);

echo "=== Error Handling Examples ===\n\n";

// Example 1: Handle authentication errors
echo "1. Handling Authentication Errors:\n";
try {
    $plans = $sdk->plans()->get();
} catch (AuthenticationException $e) {
    echo "   ✗ Authentication Error: " . $e->getMessage() . "\n";
    echo "   Status Code: " . $e->getStatusCode() . "\n";
    echo "   Action: Check client ID and secret\n";
} catch (\Exception $e) {
    echo "   ✗ Unexpected error: " . $e->getMessage() . "\n";
}
echo "\n";

// Example 2: Handle validation errors
echo "2. Handling Validation Errors:\n";
try {
    $order = $sdk->orders()->create([
        'plan_id' => -1, // Invalid plan ID
        'quantity' => 'invalid', // Should be integer
    ]);
} catch (ValidationException $e) {
    echo "   ✗ Validation Error: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getErrors')) {
        foreach ($e->getErrors() as $field => $message) {
            echo "   - $field: $message\n";
        }
    }
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Example 3: Handle not found errors
echo "3. Handling Resource Not Found Errors:\n";
try {
    $plan = $sdk->plans()->find(999999);
} catch (ResourceNotFoundException $e) {
    echo "   ✗ Resource Not Found: " . $e->getMessage() . "\n";
    echo "   Status Code: " . $e->getStatusCode() . " (404)\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Example 4: Handle rate limiting errors
echo "4. Handling Rate Limit Errors:\n";
try {
    // This would require many rapid requests to trigger
    for ($i = 0; $i < 100; $i++) {
        $sdk->plans()->get();
    }
} catch (RateLimitException $e) {
    echo "   ⚠ Rate Limited: " . $e->getMessage() . "\n";
    echo "   Retry After: " . $e->getRetryAfter() . " seconds\n";
    echo "   Action: Wait before retrying\n";
} catch (\Exception $e) {
    // Expected for demo
}
echo "\n";

// Example 5: Handle server errors
echo "5. Handling Server Errors:\n";
try {
    // This would require a server error to actually trigger
    $plans = $sdk->plans()->get();
} catch (ServerException $e) {
    echo "   ✗ Server Error: " . $e->getMessage() . "\n";
    echo "   Status Code: " . $e->getStatusCode() . "\n";
    if ($e->getStatusCode() === 503) {
        echo "   Action: Server is under maintenance, retry later\n";
    }
} catch (\Exception $e) {
    // Other errors handled differently
}
echo "\n";

// Example 6: Handle connection errors
echo "6. Handling Connection Errors:\n";
try {
    $sdkWithInvalidUrl = new TouristEsim(
        clientId: 'test',
        clientSecret: 'test',
        options: ['base_url' => 'https://invalid-domain-12345.local']
    );
    $plans = $sdkWithInvalidUrl->plans()->get();
} catch (ConnectionException $e) {
    echo "   ✗ Connection Error: " . $e->getMessage() . "\n";
    if ($e->getMessage() !== null) {
        if (strpos($e->getMessage(), 'timeout') !== false) {
            echo "   Type: Request timeout\n";
            echo "   Action: Check network or increase timeout\n";
        } elseif (strpos($e->getMessage(), 'resolve') !== false) {
            echo "   Type: DNS resolution failed\n";
            echo "   Action: Check domain and DNS settings\n";
        } elseif (strpos($e->getMessage(), 'refused') !== false) {
            echo "   Type: Connection refused\n";
            echo "   Action: Check if server is running\n";
        }
    }
} catch (\Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Example 7: Generic error handling for all exceptions
echo "7. Generic Error Handling:\n";
try {
    $plan = $sdk->plans()->find(1);
} catch (ApiException $e) {
    echo "   ✗ API Error\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   Status: " . $e->getStatusCode() . "\n";
    
    if (method_exists($e, 'getRequestId')) {
        echo "   Request ID: " . $e->getRequestId() . "\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Unexpected Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Example 8: Error context and retry logic
echo "8. Error Handling with Retry Logic:\n";
function fetchPlansWithRetry($sdk, $maxRetries = 3) {
    $retries = 0;
    $lastException = null;
    
    while ($retries < $maxRetries) {
        try {
            return $sdk->plans()->get(['per_page' => 5]);
        } catch (RateLimitException $e) {
            $retryAfter = $e->getRetryAfter();
            echo "   Attempt " . ($retries + 1) . ": Rate limited. Waiting $retryAfter seconds...\n";
            sleep($retryAfter);
        } catch (ConnectionException $e) {
            echo "   Attempt " . ($retries + 1) . ": Connection error. Retrying...\n";
            sleep(2);
        } catch (ApiException $e) {
            $lastException = $e;
            if ($e->getStatusCode() >= 500) {
                echo "   Attempt " . ($retries + 1) . ": Server error. Retrying...\n";
                sleep(1);
            } else {
                throw $e; // Don't retry client errors
            }
        }
        $retries++;
    }
    
    if ($lastException) {
        throw $lastException;
    }
    return null;
}

try {
    echo "   Fetching plans with automatic retry...\n";
    // This will work or throw after max retries
    // $plans = fetchPlansWithRetry($sdk);
    // echo "   ✓ Success\n";
} catch (\Exception $e) {
    echo "   ✗ Failed after retries: " . $e->getMessage() . "\n";
}
echo "\n";

// Example 9: Logging errors
echo "9. Error Logging Best Practices:\n";
function logError($exception, $context = []) {
    if ($exception instanceof ApiException) {
        $log = sprintf(
            "[%s] %s | Code: %d | Status: %d | Message: %s",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getCode(),
            $exception->getStatusCode(),
            $exception->getMessage()
        );
    } else {
        $log = sprintf(
            "[%s] %s | Message: %s",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage()
        );
    }
    
    if (!empty($context)) {
        $log .= " | Context: " . json_encode($context);
    }
    
    echo "   " . $log . "\n";
}

try {
    // Simulate an error
    throw new ValidationException("Invalid plan data", 422);
} catch (\Exception $e) {
    echo "   Logging error:\n";
    logError($e, ['action' => 'create_order', 'plan_id' => 123]);
}
