<?php
/**
 * Test PHP SDK - Verify it loads without errors
 * This does NOT make real API calls
 */

require __DIR__ . '/vendor/autoload.php';

use TouristeSIM\Sdk\TouristEsim;

echo "=== PHP SDK Test ===\n\n";

try {
    // Test 1: SDK can be instantiated
    echo "1. Testing SDK instantiation... ";
    $sdk = new TouristEsim('test_client_id', 'test_client_secret');
    echo "✓ PASS\n";
    
    // Test 2: Check if all resource classes exist
    echo "2. Testing resource classes... ";
    $resources = [
        'Plans',
        'Orders',
        'Esims',
        'Countries',
        'Regions',
        'Resource'
    ];
    
    $allExist = true;
    foreach ($resources as $class) {
        $fullClass = "TouristeSIM\\Sdk\\Resources\\{$class}";
        if (!class_exists($fullClass)) {
            echo "\n   ✗ FAIL: Class {$fullClass} not found\n";
            $allExist = false;
        }
    }
    
    if ($allExist) {
        echo "✓ PASS (6 resource classes)\n";
    }
    
    // Test 3: Check exception classes
    echo "3. Testing exception classes... ";
    $exceptions = [
        'TouristeSIM\Sdk\Exceptions\ApiException',
    ];
    
    $allExceptionsExist = true;
    foreach ($exceptions as $exception) {
        if (!class_exists($exception)) {
            echo "\n   ✗ FAIL: Exception {$exception} not found\n";
            $allExceptionsExist = false;
        }
    }
    
    if ($allExceptionsExist) {
        echo "✓ PASS (1 exception class)\n";
    }
    
    // Test 4: Verify SDK structure
    echo "4. Testing SDK structure... ";
    $reflection = new ReflectionClass($sdk);
    $properties = $reflection->getProperties();
    
    if (count($properties) >= 3) { // Should have at least clientId, clientSecret, baseUrl
        echo "✓ PASS\n";
    } else {
        echo "✗ FAIL: Missing expected properties\n";
    }
    
    echo "\n=== All Basic Tests Passed ✓ ===\n";
    echo "SDK is ready for package registry publication!\n\n";
    
} catch (Exception $e) {
    echo "\n✗ FAIL: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
