<?php

namespace TouristeSIM\Sdk\Resources;

use TouristeSIM\Sdk\Models\Plan;
use TouristeSIM\Sdk\Support\Collection;

/**
 * Plans Resource
 * 
 * Usage:
 * $plans = $sdk->plans()->get(['country' => 'AL', 'per_page' => 10]);
 * $plan = $sdk->plans()->find(123);
 * $valid = $sdk->plans()->validate(['plan_id' => 123, 'quantity' => 5]);
 */
class Plans extends Resource
{
    /**
     * Get filtered list of plans
     * 
     * @param array $filters Optional filters:
     *   - country: ISO country code (e.g., 'AL')
     *   - region: Region slug
     *   - type: 'local', 'regional', or 'global'
     *   - data_min: Minimum data in GB
     *   - data_max: Maximum data in GB
     *   - validity_min: Minimum validity in days
     *   - validity_max: Maximum validity in days
     *   - price_min: Minimum price
     *   - price_max: Maximum price
     *   - reloadable: true/false
     *   - sort_by: 'purchase_price', 'data_volume', 'validity', 'created_at', 'name'
     *   - sort_order: 'asc' or 'desc'
     *   - page: Page number (default: 1)
     *   - per_page: Results per page, max 100 (default: 50)
     * 
     * @return PaginatedCollection<Plan>
     */
    public function get(array $filters = []): PaginatedCollection
    {
        $response = $this->client->get('/plans', $filters);

        return new PaginatedCollection(
            Collection::make($response['data']['plans'] ?? [], Plan::class),
            $response['data']['pagination'] ?? []
        );
    }

    /**
     * Get single plan by ID
     * 
     * @param int $id Plan ID
     * @return Plan
     */
    public function find(int $id): Plan
    {
        $response = $this->client->get("/plans/{$id}");
        return new Plan($response['data']);
    }

    /**
     * Validate plan availability and get pricing
     * 
     * @param int $planId Plan ID
     * @param int $quantity Quantity to purchase
     * @return array Validation result with pricing info
     */
    public function validate(int $planId, int $quantity = 1): array
    {
        return $this->client->post('/plans/validate', [
            'plan_id' => $planId,
            'quantity' => $quantity,
        ])['data'];
    }

    /**
     * Get plans for specific country
     * 
     * @param string $countryCode ISO country code (e.g., 'AL')
     * @param int $perPage Results per page
     * @return PaginatedCollection<Plan>
     */
    public function byCountry(string $countryCode, int $perPage = 50): PaginatedCollection
    {
        return $this->get([
            'country' => strtoupper($countryCode),
            'per_page' => $perPage,
        ]);
    }

    /**
     * Get regional plans
     * 
     * @param string $regionSlug Region slug
     * @param int $perPage Results per page
     * @return PaginatedCollection<Plan>
     */
    public function byRegion(string $regionSlug, int $perPage = 50): PaginatedCollection
    {
        return $this->get([
            'region' => $regionSlug,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Get global plans
     * 
     * @param int $perPage Results per page
     * @return PaginatedCollection<Plan>
     */
    public function global(int $perPage = 50): PaginatedCollection
    {
        return $this->get([
            'type' => 'global',
            'per_page' => $perPage,
        ]);
    }
}
