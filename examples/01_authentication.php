<?php
/**
 * Example 1: Authentication
 * 
 * Demonstrates how to initialize the SDK and handle OAuth authentication.
 * The SDK automatically handles token acquisition, caching, and refresh.
 */

require __DIR__ . '/../vendor/autoload.php';

use TouristeSIM\Sdk\TouristEsim;

// Initialize the SDK with your credentials
$sdk = new TouristEsim(
    clientId: getenv('TOURIST_ESIM_CLIENT_ID') ?: 'your-client-id',
    clientSecret: getenv('TOURIST_ESIM_CLIENT_SECRET') ?: 'your-client-secret',
    options: [
        'base_url' => 'https://api.touristesim.net/v1',
        'mode' => 'sandbox', // Use 'production' for live API
        'timeout' => 30,
        'connect_timeout' => 10,
    ]
);

echo "✓ SDK initialized successfully\n";
echo "✓ Client ID: " . (getenv('TOURIST_ESIM_CLIENT_ID') ? 'Set from environment' : 'Using default') . "\n";

// Try to fetch account balance to verify authentication
try {
    $balance = $sdk->balance()->get();
    echo "✓ Authentication successful!\n";
    echo "  Account Balance: " . $balance['balance'] . " " . $balance['currency'] . "\n";
} catch (\Exception $e) {
    echo "✗ Authentication failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✓ Token is automatically managed:\n";
echo "  - OAuth 2.0 Client Credentials flow\n";
echo "  - Tokens cached in sys_get_temp_dir()\n";
echo "  - Auto-refresh 60 seconds before expiration\n";
echo "  - No manual token management required\n";
