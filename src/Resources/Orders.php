<?php

namespace TouristeSIM\Sdk\Resources;

use TouristeSIM\Sdk\Models\Order;
use TouristeSIM\Sdk\Support\{Collection, PaginatedCollection};

/**
 * Orders Resource
 * 
 * Usage:
 * $orders = $sdk->orders()->all();
 * $order = $sdk->orders()->create(['plans' => [['plan_slug' => 'vietnam_100mb_7days_7e87c5', 'quantity' => 1]]]);
 * $order = $sdk->orders()->find('PO-260519MKAUVE');
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
     * Get order by order number
     *
     * @param string $orderNumber e.g. 'PO-260519MKAUVE'
     */
    public function find(string $orderNumber): Order
    {
        $response = $this->client->get("/orders/{$orderNumber}");
        return new Order($response['data']);
    }

    /**
     * Create new order
     *
     * @param array $data ['plans' => [['plan_slug' => '...', 'quantity' => 1]], 'customer' => [...]]
     */
    public function create(array $data): Order
    {
        $response = $this->client->post('/orders', $data);
        return new Order($response['data']);
    }
}
