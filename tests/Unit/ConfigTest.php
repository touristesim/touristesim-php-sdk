<?php

namespace TouristeSIM\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TouristeSIM\Sdk\Config;

class ConfigTest extends TestCase
{
    public function test_config_initialization()
    {
        $config = new Config(
            clientId: 'test-client',
            clientSecret: 'test-secret',
            options: [
                'base_url' => 'https://api.example.com',
                'mode' => 'sandbox',
            ]
        );
        
        $this->assertEquals('test-client', $config->getClientId());
        $this->assertEquals('test-secret', $config->getClientSecret());
    }

    public function test_config_default_values()
    {
        $config = new Config('client', 'secret');
        
        $this->assertEquals('https://api.touristesim.net/v1', $config->getBaseUrl());
        $this->assertTrue($config->shouldVerifySSL());
        $this->assertEquals(30, $config->getTimeout());
    }

    public function test_config_sandbox_mode()
    {
        $config = new Config('client', 'secret', ['mode' => 'sandbox']);
        
        $this->assertTrue($config->isSandbox());
    }

    public function test_config_production_mode()
    {
        $config = new Config('client', 'secret', ['mode' => 'production']);
        
        $this->assertFalse($config->isSandbox());
    }

    public function test_config_custom_base_url()
    {
        $customUrl = 'https://custom.example.com/v1';
        $config = new Config('client', 'secret', ['base_url' => $customUrl]);
        
        $this->assertEquals($customUrl, $config->getBaseUrl());
    }

    public function test_config_http_client_options()
    {
        $config = new Config('client', 'secret', [
            'timeout' => 60,
            'connect_timeout' => 20,
        ]);
        
        $options = $config->getHttpClientOptions();
        
        $this->assertEquals(60, $options['timeout']);
        $this->assertEquals(20, $options['connect_timeout']);
    }

    public function test_config_ssl_verification_disabled()
    {
        $config = new Config('client', 'secret', ['verify_ssl' => false]);
        
        $this->assertFalse($config->shouldVerifySSL());
    }
}
