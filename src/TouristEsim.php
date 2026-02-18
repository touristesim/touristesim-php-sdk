<?php

namespace TouristeSIM\Sdk;

use TouristeSIM\Sdk\Auth\OAuthClient;
use TouristeSIM\Sdk\Resources\{
    Plans,
    Countries,
    Regions,
    Orders,
    Esims,
    Balance,
    Webhooks
};

/**
 * Tourist eSIM Partner SDK - Main Entry Point
 * 
 * Usage:
 * 
 * $sdk = new TouristEsim('client_id', 'client_secret');
 * 
 * // Get plans
 * $plans = $sdk->plans()->get(['country' => 'AL']);
 * 
 * // Create order
 * $order = $sdk->orders()->create(['plan_id' => 123, 'quantity' => 5]);
 * 
 * // Check balance
 * $balance = $sdk->balance()->get();
 */
class TouristEsim
{
    private Config $config;
    private OAuthClient $oauth;
    private HttpClient $httpClient;

    // Resource instances
    private ?Plans $plans = null;
    private ?Countries $countries = null;
    private ?Regions $regions = null;
    private ?Orders $orders = null;
    private ?Esims $esims = null;
    private ?Balance $balance = null;
    private ?Webhooks $webhooks = null;

    /**
     * Initialize SDK with credentials
     * 
     * @param string $clientId OAuth client ID
     * @param string $clientSecret OAuth client secret
     * @param array $options Configuration options:
     *   - base_url: API base URL (default: https://api.touristesim.net/v1)
     *   - mode: 'sandbox' or 'production' (default: sandbox)
     *   - verify_ssl: Verify SSL certificates (default: true)
     *   - timeout: Request timeout in seconds (default: 30)
     *   - connect_timeout: Connection timeout in seconds (default: 10)
     */
    public function __construct(string $clientId, string $clientSecret, array $options = [])
    {
        $this->config = new Config($clientId, $clientSecret, $options);
        $this->oauth = new OAuthClient($this->config);
        $this->httpClient = new HttpClient($this->config, $this->oauth);
    }

    /**
     * Plans resource - Fetch and validate plans
     */
    public function plans(): Plans
    {
        if (!$this->plans) {
            $this->plans = new Plans($this->httpClient);
        }
        return $this->plans;
    }

    /**
     * Countries resource - List countries with available plans
     */
    public function countries(): Countries
    {
        if (!$this->countries) {
            $this->countries = new Countries($this->httpClient);
        }
        return $this->countries;
    }

    /**
     * Regions resource - Get regional groups
     */
    public function regions(): Regions
    {
        if (!$this->regions) {
            $this->regions = new Regions($this->httpClient);
        }
        return $this->regions;
    }

    /**
     * Orders resource - Create and manage orders
     */
    public function orders(): Orders
    {
        if (!$this->orders) {
            $this->orders = new Orders($this->httpClient);
        }
        return $this->orders;
    }

    /**
     * eSIMs resource - Manage eSIM cards
     */
    public function esims(): Esims
    {
        if (!$this->esims) {
            $this->esims = new Esims($this->httpClient);
        }
        return $this->esims;
    }

    /**
     * Balance resource - Check account balance
     */
    public function balance(): Balance
    {
        if (!$this->balance) {
            $this->balance = new Balance($this->httpClient);
        }
        return $this->balance;
    }

    /**
     * Webhooks resource - Manage webhooks
     */
    public function webhooks(): Webhooks
    {
        if (!$this->webhooks) {
            $this->webhooks = new Webhooks($this->httpClient);
        }
        return $this->webhooks;
    }

    /**
     * Get configuration
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Get HTTP client
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Get OAuth client
     */
    public function getOAuthClient(): OAuthClient
    {
        return $this->oauth;
    }

    /**
     * Get SDK version
     */
    public static function VERSION(): string
    {
        return '1.0.0';
    }
}
