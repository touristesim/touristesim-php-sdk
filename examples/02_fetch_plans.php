<?php
/**
 * Example 2: Fetching Plans
 * 
 * Demonstrates how to fetch eSIM plans by country, region, or globally,
 * with filtering options and pagination support.
 */

require __DIR__ . '/../vendor/autoload.php';

use TouristeSIM\Sdk\TouristEsim;

$sdk = new TouristEsim(
    clientId: getenv('TOURIST_ESIM_CLIENT_ID') ?: 'your-client-id',
    clientSecret: getenv('TOURIST_ESIM_CLIENT_SECRET') ?: 'your-client-secret'
);

echo "=== Fetching Plans ===\n\n";

// Example 1: Get all plans with filters
echo "1. Get all plans (first 10):\n";
$plans = $sdk->plans()->get([
    'per_page' => 10,
    'sort_by' => 'price',
    'sort_order' => 'asc'
]);

echo "   Found " . $plans->getTotal() . " plans total\n";
echo "   Current page: " . $plans->getCurrentPage() . " / " . $plans->getLastPage() . "\n\n";

foreach ($plans as $plan) {
    printf("   %-40s %6.2f %3s  %3d GB  %d days\n",
        substr($plan['name'], 0, 40),
        $plan['price'],
        $plan['currency'],
        intval($plan['data'] / 1024),
        $plan['validity_days']
    );
}

// Example 2: Get plans by country
echo "\n2. Get plans for United States:\n";
$usPlans = $sdk->plans()->byCountry('US', 5);
echo "   Found " . count($usPlans) . " plans:\n";
foreach ($usPlans as $plan) {
    echo "   - " . $plan['name'] . " ($" . $plan['price'] . ")\n";
}

// Example 3: Get plans by region
echo "\n3. Get plans for Europe region:\n";
$europePlans = $sdk->plans()->byRegion('europe', 5);
echo "   Found " . count($europePlans) . " plans:\n";
foreach ($europePlans as $plan) {
    echo "   - " . $plan['name'] . " ($" . $plan['price'] . ")\n";
}

// Example 4: Get global plans
echo "\n4. Get global plans (work worldwide):\n";
$globalPlans = $sdk->plans()->global(5);
echo "   Found " . count($globalPlans) . " plans:\n";
foreach ($globalPlans as $plan) {
    echo "   - " . $plan['name'] . " ($" . $plan['price'] . ")\n";
}

// Example 5: Get a specific plan
echo "\n5. Get specific plan (if ID exists):\n";
try {
    $plan = $sdk->plans()->find(1);
    printf("   Plan ID: %d\n", $plan['id']);
    printf("   Name: %s\n", $plan['name']);
    printf("   Price: %s %s\n", $plan['price'], $plan['currency']);
    printf("   Data: %d MB\n", $plan['data']);
    printf("   Validity: %d days\n", $plan['validity_days']);
    printf("   Type: %s\n", $plan['type']);
} catch (\Exception $e) {
    echo "   Plan not found or error: " . $e->getMessage() . "\n";
}

// Example 6: Validate plan before purchase
echo "\n6. Validate plan before purchase:\n";
try {
    $validation = $sdk->plans()->validate(planId: 1, quantity: 5);
    printf("   Plan valid: %s\n", $validation['is_valid'] ? 'Yes' : 'No');
    printf("   Total price: %s\n", $validation['total_price']);
    printf("   Currency: %s\n", $validation['currency']);
} catch (\Exception $e) {
    echo "   Validation error: " . $e->getMessage() . "\n";
}
