<?php
/**
 * Example 3: Creating and Managing Orders
 *
 * Demonstrates how to create orders and retrieve order details.
 * Orders are identified by order_number (e.g., 'PO-260519MKAUVE').
 * To request a cancellation or refund, contact Tourist eSIM support.
 */

require __DIR__ . '/../vendor/autoload.php';

use TouristeSIM\Sdk\TouristEsim;

$sdk = new TouristEsim(
    clientId: getenv('TOURIST_ESIM_CLIENT_ID') ?: 'your-client-id',
    clientSecret: getenv('TOURIST_ESIM_CLIENT_SECRET') ?: 'your-client-secret'
);

echo "=== Managing Orders ===\n\n";

// Example 1: Fetch available plans
echo "1. Finding a plan to order:\n";
$plans = $sdk->plans()->get(['per_page' => 1]);

if (count($plans) === 0) {
    echo "   ✗ No plans available\n";
    exit(1);
}

$plan = $plans->first();
$planSlug = $plan['plan_slug'];
echo "   ✓ Selected plan: " . $plan['name'] . " (slug: $planSlug)\n";
echo "   Price: " . $plan['price'] . " " . ($plan['currency'] ?? 'USD') . "\n\n";

// Example 2: Create an order
echo "2. Creating an order:\n";
try {
    $order = $sdk->orders()->create([
        'plans' => [
            ['plan_slug' => $planSlug, 'quantity' => 1],
        ],
        'customer' => ['email' => 'customer@example.com', 'name' => 'Jane Doe'],
    ]);

    $orderNumber = $order['order_number'];
    echo "   ✓ Order created successfully!\n";
    printf("   Order Number: %s\n", $orderNumber);
    printf("   Status: %s\n", $order['status']);
    printf("   Amount: %s %s\n", $order['amount'], $order['currency'] ?? 'USD');
    printf("   Created: %s\n\n", $order['created_at']);
} catch (\Exception $e) {
    echo "   ✗ Failed to create order: " . $e->getMessage() . "\n";
    exit(1);
}

// Example 3: Retrieve order details
echo "3. Retrieving order details:\n";
try {
    $order = $sdk->orders()->find($orderNumber);
    printf("   Order Number: %s\n", $order['order_number']);
    printf("   Status: %s\n", $order['status']);
    printf("   Amount: %s %s\n", $order['amount'], $order['currency'] ?? 'USD');
    printf("   Items: %d\n", $order['items_count']);
    printf("   Created: %s\n", $order['created_at']);
    printf("   Updated: %s\n\n", $order['updated_at']);
} catch (\Exception $e) {
    echo "   ✗ Failed to retrieve order: " . $e->getMessage() . "\n";
}

// Example 4: List all orders (with filters)
echo "4. Listing recent orders:\n";
try {
    $orders = $sdk->orders()->all(['per_page' => 5]);

    echo "   Found " . count($orders) . " recent orders:\n";
    foreach ($orders as $o) {
        printf("   - %s: %s (%s %s)\n", $o['order_number'], $o['status'], $o['amount'], $o['currency'] ?? 'USD');
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ✗ Failed to list orders: " . $e->getMessage() . "\n";
}

// Example 5: Check order status
echo "5. Checking order status:\n";
try {
    $order = $sdk->orders()->find($orderNumber);
    $status = $order['status'];

    $statusFlow = [
        'pending'    => '⏳ Pending - eSIM being provisioned',
        'processing' => '⚙ Processing - provisioning in progress',
        'completed'  => '✓ Completed - eSIMs ready',
        'failed'     => '✗ Failed - provisioning failed',
        'cancelled'  => '⊗ Cancelled',
    ];

    echo "   Current status: " . ($statusFlow[$status] ?? $status) . "\n";
    echo "\n   Note: To request a cancellation or refund, contact Tourist eSIM support.\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}
