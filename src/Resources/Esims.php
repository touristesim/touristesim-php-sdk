<?php

namespace TouristeSIM\Sdk\Resources;

use TouristeSIM\Sdk\Models\Esim;
use TouristeSIM\Sdk\Support\{Collection, PaginatedCollection};

/**
 * eSIMs Resource
 * 
 * Usage:
 * $esims = $sdk->esims()->all();
 * $esim = $sdk->esims()->find('iccid123');
 * $usage = $sdk->esims()->usage('iccid123');
 */
class Esims extends Resource
{
    /**
     * Get all eSIMs
     */
    public function all(array $filters = []): PaginatedCollection
    {
        $response = $this->client->get('/esims', $filters);
        return new PaginatedCollection(
            Collection::make($response['data']['esims'] ?? [], Esim::class),
            $response['data']['pagination'] ?? []
        );
    }

    /**
     * Get eSIM by ICCID
     */
    public function find(string $iccid): Esim
    {
        $response = $this->client->get("/esims/{$iccid}");
        return new Esim($response['data']);
    }

    /**
     * Get eSIM usage/data consumption
     */
    public function usage(string $iccid): array
    {
        $response = $this->client->get("/esims/{$iccid}/usage");
        return $response['data'] ?? [];
    }

    /**
     * Get available topup packages for eSIM
     */
    public function topupPackages(string $iccid): Collection
    {
        $response = $this->client->get("/esims/{$iccid}/topups");
        return Collection::make($response['data']['packages'] ?? [], 'array');
    }

    /**
     * Purchase and apply topup
     */
    public function topup(string $iccid, int $packageId): array
    {
        $response = $this->client->post("/esims/{$iccid}/topup", [
            'package_id' => $packageId,
        ]);
        return $response['data'] ?? [];
    }

    /**
     * Get setup instructions for eSIM
     */
    public function instructions(string $iccid): string
    {
        $response = $this->client->get("/esims/{$iccid}/instructions");
        return $response['data']['instructions'] ?? '';
    }

    /**
     * Send setup email to customer
     */
    public function sendEmail(string $iccid, string $email): bool
    {
        $this->client->post("/esims/{$iccid}/send-email", [
            'email' => $email,
        ]);
        return true;
    }
}
