<?php

namespace TouristeSIM\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TouristeSIM\Sdk\Models\Plan;
use TouristeSIM\Sdk\Models\Country;
use TouristeSIM\Sdk\Models\Order;

class ModelsTest extends TestCase
{
    public function test_plan_model_creation()
    {
        $data = [
            'id' => 1,
            'name' => 'Global 10GB',
            'price' => '99.99',
            'currency' => 'USD',
            'validity_days' => '30',
            'data' => '10240',
            'type' => 'global',
            'reloadable' => true,
        ];
        
        $plan = new Plan($data);
        
        $this->assertEquals(1, $plan['id']);
        $this->assertEquals('Global 10GB', $plan['name']);
        $this->assertEquals(99.99, $plan['price']);
    }

    public function test_plan_model_type_casting()
    {
        $data = [
            'id' => '123',
            'price' => '45.50',
            'validity_days' => '30',
            'reloadable' => 'true',
        ];
        
        $plan = new Plan($data);
        
        $this->assertIsInt($plan['id']);
        $this->assertIsFloat($plan['price']);
        $this->assertIsBool($plan['reloadable']);
    }

    public function test_country_model_creation()
    {
        $data = [
            'code' => 'US',
            'name' => 'United States',
            'slug' => 'united-states',
            'plans_count' => '150',
            'is_featured' => true,
        ];
        
        $country = new Country($data);
        
        $this->assertEquals('US', $country['code']);
        $this->assertEquals('United States', $country['name']);
        $this->assertEquals(150, $country['plans_count']);
    }

    public function test_order_model_helpers()
    {
        $data = [
            'id' => 1,
            'status' => 'completed',
            'plan_id' => 123,
            'quantity' => '5',
            'total_price' => '199.95',
            'currency' => 'USD',
        ];
        
        $order = new Order($data);
        
        $this->assertEquals('completed', $order->getStatus());
        $this->assertTrue($order->isCompleted());
        $this->assertFalse($order->isPending());
    }

    public function test_order_model_status_helpers()
    {
        $pendingOrder = new Order(['status' => 'pending']);
        $this->assertTrue($pendingOrder->isPending());
        $this->assertFalse($pendingOrder->isCompleted());
        
        $cancelledOrder = new Order(['status' => 'cancelled']);
        $this->assertTrue($cancelledOrder->isCancelled());
    }

    public function test_plan_model_helpers()
    {
        $localPlan = new Plan(['id' => 1, 'type' => 'local']);
        $this->assertTrue($localPlan->isLocal());
        $this->assertFalse($localPlan->isGlobal());
        
        $globalPlan = new Plan(['id' => 2, 'type' => 'global']);
        $this->assertTrue($globalPlan->isGlobal());
        
        $regionalPlan = new Plan(['id' => 3, 'type' => 'regional']);
        $this->assertTrue($regionalPlan->isRegional());
    }

    public function test_model_fill_method()
    {
        $plan = new Plan([]);
        $plan->fill([
            'id' => 1,
            'name' => 'Test Plan',
            'price' => 50,
        ]);
        
        $this->assertEquals(1, $plan['id']);
        $this->assertEquals('Test Plan', $plan['name']);
        $this->assertEquals(50, $plan['price']);
    }

    public function test_model_to_array()
    {
        $data = [
            'id' => 1,
            'name' => 'Plan A',
            'price' => 99.99,
        ];
        
        $plan = new Plan($data);
        $array = $plan->toArray();
        
        $this->assertEquals($data, $array);
    }

    public function test_model_to_json()
    {
        $data = [
            'id' => 1,
            'name' => 'Plan A',
            'price' => 99.99,
        ];
        
        $plan = new Plan($data);
        $json = $plan->toJson();
        
        $decoded = json_decode($json, true);
        $this->assertEquals($data, $decoded);
    }
}
