<?php
/**
 * Example 4: Managing eSIMs
 * 
 * Demonstrates how to check eSIM status, data usage, topup packages,
 * and setup instructions.
 */

require __DIR__ . '/../vendor/autoload.php';

use TouristeSIM\Sdk\TouristEsim;

$sdk = new TouristEsim(
    clientId: getenv('TOURIST_ESIM_CLIENT_ID') ?: 'your-client-id',
    clientSecret: getenv('TOURIST_ESIM_CLIENT_SECRET') ?: 'your-client-secret'
);

echo "=== Managing eSIMs ===\n\n";

// Example 1: List all eSIMs
echo "1. Fetching all eSIMs:\n";
try {
    $esims = $sdk->esims()->all([
        'limit' => 10,
        'status' => 'active'
    ]);
    
    echo "   Found " . count($esims) . " active eSIMs\n";
    
    if (count($esims) > 0) {
        foreach ($esims as $esim) {
            printf("   ICCID: %s | Status: %s | Validity: %s\n",
                substr($esim['iccid'], -8),
                $esim['status'],
                $esim['validity_end'] ?? 'N/A'
            );
        }
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ✗ Error fetching eSIMs: " . $e->getMessage() . "\n";
    exit(1);
}

// Example 2: Get eSIM details (using demo ICCID)
$sampleIccid = '8955001000000000000';
echo "2. Getting eSIM details (sample ICCID: $sampleIccid):\n";
try {
    $esim = $sdk->esims()->find($sampleIccid);
    printf("   ICCID: %s\n", $esim['iccid']);
    printf("   Status: %s\n", $esim['status']);
    printf("   Balance Data: %s MB\n", $esim['balance_data']);
    printf("   Validity End: %s\n", $esim['validity_end']);
    printf("   Activation Date: %s\n", $esim['activation_date'] ?? 'Not activated');
    
    // Check if eSIM properties are available
    if (isset($esim['plan'])) {
        printf("   Plan: %s\n", $esim['plan']['name']);
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ℹ Sample eSIM not found: " . $e->getMessage() . "\n";
    echo "   (This is expected for demo purposes)\n\n";
}

// Example 3: Check eSIM data usage
echo "3. Checking eSIM data usage:\n";
try {
    $usage = $sdk->esims()->usage($sampleIccid);
    printf("   Total Data: %s MB\n", $usage['total_data']);
    printf("   Used Data: %s MB\n", $usage['used_data']);
    printf("   Remaining Data: %s MB\n", $usage['remaining_data']);
    printf("   Usage Percentage: %.1f%%\n", ($usage['used_data'] / $usage['total_data']) * 100);
    echo "\n";
} catch (\Exception $e) {
    echo "   ℹ Could not fetch usage data: " . $e->getMessage() . "\n";
    echo "   (This is expected for demo purposes)\n\n";
}

// Example 4: Get available topup packages
echo "4. Fetching available topup packages:\n";
try {
    $packages = $sdk->esims()->topupPackages($sampleIccid);
    
    echo "   Available topup packages:\n";
    if (count($packages) > 0) {
        foreach ($packages as $pkg) {
            printf("   - Package ID: %d | Data: %s | Price: %s\n",
                $pkg['id'],
                $pkg['data'],
                $pkg['price']
            );
        }
    } else {
        echo "   ℹ No topup packages available\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   ℹ Could not fetch packages: " . $e->getMessage() . "\n\n";
}

// Example 5: Purchase a topup (example)
echo "5. Purchasing a topup (demo):\n";
echo "   To purchase a topup:\n";
try {
    echo "   \$topup = \$sdk->esims()->topup(\n";
    echo "       iccid: '$sampleIccid',\n";
    echo "       packageId: 123\n";
    echo "   );\n";
    echo "   (This would charge your account)\n";
} catch (\Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Example 6: Get setup instructions
echo "6. Getting setup instructions:\n";
try {
    $instructions = $sdk->esims()->instructions($sampleIccid);
    echo "   Setup Instructions:\n";
    echo "   " . wordwrap($instructions, 70, "\n   ") . "\n";
    echo "\n";
} catch (\Exception $e) {
    echo "   ℹ Could not fetch instructions: " . $e->getMessage() . "\n\n";
}

// Example 7: Send setup email
echo "7. Sending setup email to customer:\n";
try {
    $sent = $sdk->esims()->sendEmail($sampleIccid, 'customer@example.com');
    echo "   ✓ Setup email sent to customer@example.com\n";
} catch (\Exception $e) {
    echo "   ✗ Failed to send email: " . $e->getMessage() . "\n";
}

echo "\n=== eSIM Statuses ===\n";
echo "  pending   - eSIM created, not yet activated\n";
echo "  active    - eSIM activated and ready to use\n";
echo "  suspended - eSIM suspended (usually no data remaining)\n";
echo "  expired   - eSIM plan validity expired\n";
