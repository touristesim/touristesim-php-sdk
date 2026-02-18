<?php

namespace TouristeSIM\Sdk\Resources;

use TouristEsim\Sdk\HttpClient;

/**
 * Base Resource class
 */
abstract class Resource
{
    protected HttpClient $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }
}
