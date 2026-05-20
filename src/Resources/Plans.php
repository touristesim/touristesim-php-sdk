<?php

namespace TouristeSIM\Sdk\Resources;

use TouristeSIM\Sdk\Models\Plan;
use TouristeSIM\Sdk\Support\Collection;

/**
 * Plans Resource
 * 
 * Usage:
 * $plans = $sdk->plans()->get(['country' => 'AL', 'per_page' => 10]);
 * $plan = $sdk->plans()->find('vietnam_100mb_7days_7e87c5');
 * $valid = $sdk->plans()->validate('vietnam_100mb_7days_7e87c5', 5);
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
     * Get single plan by slug
     *
     * @param string $slug Plan slug (e.g., 'vietnam_100mb_7days_7e87c5')
     * @return Plan
     */
    public function find(string $slug): Plan
    {
        $response = $this->client->get("/plans/{$slug}");
        return new Plan($response['data']);
    }

    /**
     * Validate plan availability and get pricing
     *
     * @param string $planSlug Plan slug (e.g., 'vietnam_100mb_7days_7e87c5')
     * @param int $quantity Quantity to purchase
     * @return array Validation result with pricing info
     */
    public function validate(string $planSlug, int $quantity = 1): array
    {
        return $this->client->post('/plans/validate', [
            'plan_slug' => $planSlug,
            'quantity'  => $quantity,
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
