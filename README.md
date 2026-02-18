# Tourist eSIM PHP SDK

Official PHP SDK for Tourist eSIM Partner API. Enable easy integration for resellers and affiliates to manage eSIM plans, orders, and customer data.

## Features

- 🔐 **OAuth 2.0 Authentication** - Secure Client Credentials flow with automatic token refresh
- 🚀 **Auto-Retry Logic** - Exponential backoff with 3 retry attempts on connection failures
- 📦 **Type-Safe Models** - Full type casting and helper methods on all models
- 💾 **Token Caching** - File-based token cache to reduce OAuth requests (1 per hour)
- ⚡ **Lazy Loading** - Resources initialized on first access for minimal memory footprint
- 🔄 **Pagination Support** - Built-in pagination for catalog queries
- 🎯 **Exception Hierarchy** - Specific exceptions for different error scenarios (Auth, Validation, RateLimit, etc.)

## Requirements

- PHP 8.1+
- cURL extension
- GuzzleHTTP 7.0+

## Installation

Install via Composer:

```bash
composer require touristesim/touristesim-php-sdk
```

## Quick Start

### Basic Setup

```php
require 'vendor/autoload.php';

use TouristeSIM\Sdk\TouristEsim;

$sdk = new TouristEsim(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret'
);

// Fetch all plans
$plans = $sdk->plans()->get();
foreach ($plans as $plan) {
    echo $plan['name'] . " - " . $plan['price'] . " " . $plan['currency'] . "\n";
}
```

### Authentication

The SDK handles OAuth 2.0 authentication automatically:

```php
$sdk = new TouristEsim(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
    options: [
        'base_url' => 'https://api.touristesim.net/v1',
        'timeout' => 30,
        'mode' => 'sandbox' // or 'production'
    ]
);

// Token is automatically acquired and cached
// Auto-refresh happens 60 seconds before expiration
```

## API Usage

### Plans

```php
// Fetch all plans with filters
$plans = $sdk->plans()->get([
    'country' => 'US',
    'type' => 'global',
    'data_min' => 1024,  // in MB
    'sort_by' => 'price',
    'page' => 1,
    'per_page' => 50
]);

// Get single plan
$plan = $sdk->plans()->find(123);

// Get plans by country
$usPlans = $sdk->plans()->byCountry('US');

// Get plans by region
$asiaPlans = $sdk->plans()->byRegion('asia');

// Get global plans
$globalPlans = $sdk->plans()->global();

// Validate plan before purchase
$validation = $sdk->plans()->validate(planId: 123, quantity: 5);
```

### Countries

```php
// Get all countries
$countries = $sdk->countries()->all();

// Find country by ISO code
$usa = $sdk->countries()->find('US');

// Search countries
$asian = $sdk->countries()->search('china');

// Get countries by region
$europeanCountries = $sdk->countries()->byRegion('europe');

// Get featured countries
$featured = $sdk->countries()->featured();
```

### Regions

```php
// Get all regional groups
$regions = $sdk->regions()->all();
```

### Orders

```php
// Create order
$order = $sdk->orders()->create([
    'plan_id' => 123,
    'quantity' => 2,
    'customer_email' => 'customer@example.com',
    'coupon_code' => 'SAVE10' // optional
]);

// Get all orders
$orders = $sdk->orders()->all(['status' => 'completed']);

// Get single order
$order = $sdk->orders()->find(456);

// Cancel order
$cancelled = $sdk->orders()->cancel(456);
```

### eSIMs

```php
// Get all eSIMs
$esims = $sdk->esims()->all(['status' => 'active']);

// Find eSIM by ICCID
$esim = $sdk->esims()->find('8955001000000000000');

// Check eSIM usage
$usage = $sdk->esims()->usage('8955001000000000000');

// Get available topup packages
$packages = $sdk->esims()->topupPackages('8955001000000000000');

// Purchase topup
$topup = $sdk->esims()->topup(
    iccid: '8955001000000000000',
    packageId: 789
);

// Get setup instructions
$instructions = $sdk->esims()->instructions('8955001000000000000');

// Send setup email
$sdk->esims()->sendEmail('8955001000000000000', 'user@example.com');
```

### Account Balance

```php
// Get account balance
$balance = $sdk->balance()->get();

// Get balance history
$history = $sdk->balance()->history(['limit' => 50]);
```

## Working with Collections

All list responses return Collection objects with helpful methods:

```php
$plans = $sdk->plans()->get();

// Filter
$expensivePlans = $plans->filter(fn($plan) => $plan['price'] > 50);

// Map
$prices = $plans->map(fn($plan) => $plan['price']);

// Sort
$sorted = $plans->sortBy('price');

// Pluck
$names = $plans->pluck('name');

// Iteration
foreach ($plans as $plan) {
    // Use plan
}
```

## Error Handling

The SDK provides specific exceptions for different error scenarios:

```php
use TouristeSIM\Sdk\Exceptions\{
    AuthenticationException,
    ValidationException,
    RateLimitException,
    ResourceNotFoundException,
    ServerException,
    ConnectionException
};

try {
    $plan = $sdk->plans()->find(999);
} catch (ResourceNotFoundException $e) {
    echo "Plan not found: " . $e->getMessage();
} catch (AuthenticationException $e) {
    echo "Authentication failed: " . $e->getMessage();
} catch (RateLimitException $e) {
    echo "Rate limited. Retry after: " . $e->getRetryAfter() . " seconds";
} catch (ValidationException $e) {
    echo "Validation error: " . json_encode($e->getErrors());
} catch (ConnectionException $e) {
    echo "Connection failed: " . $e->getMessage();
} catch (ServerException $e) {
    echo "Server error: " . $e->getMessage();
}
```

## Configuration Options

```php
$options = [
    'base_url' => 'https://api.touristesim.net/v1', // API base URL
    'timeout' => 30,                                  // Request timeout (seconds)
    'connect_timeout' => 10,                          // Connection timeout (seconds)
    'mode' => 'sandbox',                              // 'sandbox' or 'production'
    'verify_ssl' => true,                             // SSL certificate verification
    'max_retries' => 3,                               // Max retry attempts
];

$sdk = new TouristEsim(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
    options: $options
);
```

## Pagination

Paginated responses include metadata:

```php
$plans = $sdk->plans()->get(['page' => 1, 'per_page' => 50]);

echo "Current page: " . $plans->getCurrentPage();
echo "Total pages: " . $plans->getLastPage();
echo "Total items: " . $plans->getTotal();
echo "Has more: " . ($plans->hasMore() ? 'Yes' : 'No');

// Iterate through paginated results
foreach ($plans as $plan) {
    // Process plan
}
```

## Token Management

Tokens are automatically managed:

```php
// Tokens are cached in sys_get_temp_dir()
// Cache file: tourist_esim_token_[hash].json
// Permissions: 0600 (owner-only readable)

// Token is automatically refreshed when expired (60-second buffer)
// No manual token management required
```

## Debugging

Enable detailed logging by catching exceptions:

```php
try {
    $plans = $sdk->plans()->get();
} catch (\Exception $e) {
    echo "Error code: " . $e->getCode() . "\n";
    echo "Error message: " . $e->getMessage() . "\n";
    
    if (method_exists($e, 'getResponse')) {
        echo "Response body: " . $e->getResponse() . "\n";
    }
}
```

## API Documentation

For complete API documentation, visit:
- **Production**: https://docs.touristesim.net/api
- **Partner Dashboard**: https://partners.touristesim.net

## Support

For issues or questions:
- Email: developers@touristesim.net
- GitHub Issues: https://github.com/touristesim/touristesim-php-sdk/issues

## License

MIT License - see LICENSE file for details

## Changelog

### v1.0.0 (2026-02-18)
- Initial public release
- OAuth 2.0 authentication with auto-refresh
- Plans, Countries, Regions, Orders, eSIMs resources
- Auto-retry with exponential backoff
- Token caching
- Collection support with filtering and mapping
- Comprehensive error handling
