<?php

namespace TouristeSIM\Sdk\Resources;

use TouristeSIM\Sdk\Models\Order;
use TouristeSIM\Sdk\Support\{Collection, PaginatedCollection};

/**
 * Orders Resource
 * 
 * Usage:
 * $orders = $sdk->orders()->all();
 * $order = $sdk->orders()->create(['plan_id' => 123, 'quantity' => 5]);
 * $order = $sdk->orders()->find(456);
 */
class Orders extends Resource
{
    /**
     * Get all partner orders
     */
    public function all(array $filters = []): PaginatedCollection
    {
        $response = $this->client->get('/orders', $filters);
        return new PaginatedCollection(
            Collection::make($response['data']['orders'] ?? [], Order::class),
            $response['data']['pagination'] ?? []
        );
    }

    /**
     * Get order by ID
     */
    public function find(int $id): Order
    {
        $response = $this->client->get("/orders/{$id}");
        return new Order($response['data']);
    }

    /**
     * Create new order
     */
    public function create(array $data): Order
    {
        $response = $this->client->post('/orders', $data);
        return new Order($response['data']);
    }

    /**
     * Cancel order
     */
    public function cancel(int $id): bool
    {
        $this->client->post("/orders/{$id}/cancel");
        return true;
    }
}
