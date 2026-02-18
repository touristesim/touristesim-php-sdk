<?php
/**
 * Example 3: Creating and Managing Orders
 * 
 * Demonstrates how to create orders, retrieve order details,
 * check order status, and cancel orders.
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
$planId = $plan['id'];
echo "   ✓ Selected plan: " . $plan['name'] . " (ID: $planId)\n";
echo "   Price: " . $plan['price'] . " " . $plan['currency'] . "\n\n";

// Example 2: Create an order
echo "2. Creating an order:\n";
try {
    $order = $sdk->orders()->create([
        'plan_id' => $planId,
        'quantity' => 2,
        'customer_email' => 'customer@example.com',
    ]);
    
    $orderId = $order['id'];
    echo "   ✓ Order created successfully!\n";
    printf("   Order ID: %d\n", $orderId);
    printf("   Status: %s\n", $order['status']);
    printf("   Total Price: %s %s\n", $order['total_price'], $order['currency']);
    printf("   Created: %s\n\n", $order['created_at']);
} catch (\Exception $e) {
    echo "   ✗ Failed to create order: " . $e->getMessage() . "\n";
    exit(1);
}

// Example 3: Retrieve order details
echo "3. Retrieving order details:\n";
try {
    $order = $sdk->orders()->find($orderId);
    printf("   Order ID: %d\n", $order['id']);
    printf("   Status: %s\n", $order['status']);
    printf("   Plan ID: %d\n", $order['plan_id']);
    printf("   Quantity: %d\n", $order['quantity']);
    printf("   Total Price: %s %s\n", $order['total_price'], $order['currency']);
    printf("   Created: %s\n", $order['created_at']);
    printf("   Updated: %s\n\n", $order['updated_at']);
} catch (\Exception $e) {
    echo "   ✗ Failed to retrieve order: " . $e->getMessage() . "\n";
}

// Example 4: List all orders (with filters)
echo "4. Listing recent orders:\n";
try {
    $orders = $sdk->orders()->all([
        'limit' => 5,
        'sort' => 'created_at:desc'
    ]);
    
    echo "   Found " . count($orders) . " recent orders:\n";
    foreach ($orders as $o) {
        printf("   - Order #%d: %s (%s)\n", $o['id'], $o['status'], $o['total_price'] . ' ' . $o['currency']);
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ✗ Failed to list orders: " . $e->getMessage() . "\n";
}

// Example 5: Check order status
echo "5. Checking order status history:\n";
try {
    $order = $sdk->orders()->find($orderId);
    $status = $order['status'];
    
    $statusFlow = [
        'pending' => '⏳ Pending - Payment processing',
        'completed' => '✓ Completed - eSIMs ready',
        'failed' => '✗ Failed - Payment failed',
        'cancelled' => '⊗ Cancelled - Order cancelled',
    ];
    
    echo "   Current status: " . ($statusFlow[$status] ?? $status) . "\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

// Example 6: Cancel an order (only if pending)
echo "\n6. Cancelling an order (if applicable):\n";
try {
    $order = $sdk->orders()->find($orderId);
    
    if ($order['status'] === 'pending') {
        $cancelled = $sdk->orders()->cancel($orderId);
        echo "   ✓ Order cancelled successfully\n";
    } else {
        echo "   ℹ Order status is '" . $order['status'] . "' - can only cancel pending orders\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Failed to cancel order: " . $e->getMessage() . "\n";
}
