<?php

namespace Tests\Feature;

use Tests\TestCase;

class ServiceGatewayCatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.service_gateway.token' => 'test-token']);
    }

    public function test_catalog_endpoint_lists_configured_services(): void
    {
        $response = $this->withHeader('X-Service-Token', 'test-token')->getJson('/api/services');

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure([
                'ok',
                'services' => [
                    'hr' => ['connection', 'resources'],
                    'payroll' => ['connection', 'resources'],
                    'attendance' => ['connection', 'resources'],
                    'recruitment' => ['connection', 'resources'],
                    'training' => ['connection', 'resources'],
                    'reporting' => ['connection', 'resources'],
                    'chatbot' => ['connection', 'resources'],
                ],
            ]);
    }
}