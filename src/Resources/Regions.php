<?php

namespace TouristeSIM\Sdk\Resources;

use TouristEsim\Sdk\Support\Collection;

/**
 * Regions Resource
 * 
 * Usage:
 * $regions = $sdk->regions()->all();
 */
class Regions extends Resource
{
    /**
     * Get all regional groups
     * 
     * @return Collection
     */
    public function all(): Collection
    {
        $response = $this->client->get('/regions');
        return Collection::make($response['data']['groups'] ?? [], 'array');
    }

    /**
     * Convert response array to collection of arrays
     */
    private function arrayToCollection(array $items): Collection
    {
        return new Collection($items);
    }
}
