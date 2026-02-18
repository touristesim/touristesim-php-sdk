<?php

namespace TouristeSIM\Sdk\Resources;

use TouristeSIM\Sdk\Models\Country;
use TouristeSIM\Sdk\Support\Collection;

/**
 * Countries Resource
 * 
 * Usage:
 * $countries = $sdk->countries()->all();
 * $albania = $sdk->countries()->find('AL');
 */
class Countries extends Resource
{
    /**
     * Get all countries with available plans
     * 
     * @param array $filters Optional filters:
     *   - search: Search by name or country code
     *   - region: Filter by region slug
     * 
     * @return Collection<Country>
     */
    public function all(array $filters = []): Collection
    {
        $response = $this->client->get('/countries', $filters);
        return Collection::make($response['data'], Country::class);
    }

    /**
     * Find country by code
     * 
     * @param string $code ISO country code (e.g., 'AL')
     * @return Country|null
     */
    public function find(string $code): ?Country
    {
        $countries = $this->all(['search' => strtoupper($code)]);
        
        foreach ($countries as $country) {
            if ($country->getCode() === strtoupper($code)) {
                return $country;
            }
        }

        return null;
    }

    /**
     * Search countries by name
     * 
     * @param string $query Search query
     * @return Collection<Country>
     */
    public function search(string $query): Collection
    {
        return $this->all(['search' => $query]);
    }

    /**
     * Get countries in a region
     * 
     * @param string $regionSlug Region slug
     * @return Collection<Country>
     */
    public function byRegion(string $regionSlug): Collection
    {
        return $this->all(['region' => $regionSlug]);
    }

    /**
     * Get featured countries
     * 
     * @return Collection<Country>
     */
    public function featured(): Collection
    {
        return $this->all()->filter(fn($country) => $country->isFeatured());
    }
}
