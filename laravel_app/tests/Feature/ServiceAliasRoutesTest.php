<?php

namespace Tests\Feature;

use Tests\TestCase;

class ServiceAliasRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.service_gateway.token' => 'test-token']);
    }

    public function test_service_alias_catalog_endpoint_lists_resources(): void
    {
        $response = $this->withHeader('X-Service-Token', 'test-token')->getJson('/api/hr');

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('service', 'hr')
            ->assertJsonStructure([
                'ok',
                'service',
                'connection',
                'resources',
            ]);
    }

    public function test_service_alias_resource_route_uses_same_validation_as_generic_gateway(): void
    {
        $response = $this->withHeader('X-Service-Token', 'test-token')->getJson('/api/hr/unknown-resource');

        $response->assertStatus(404)
            ->assertJsonPath('ok', false);
    }

    public function test_read_only_resource_rejects_write_requests(): void
    {
        $response = $this->withHeader('X-Service-Token', 'test-token')->postJson('/api/attendance/attendance-summaries', [
            'MaNV' => 'NV01',
            'Thang' => 4,
            'Nam' => 2026,
        ]);

        $response->assertStatus(405)
            ->assertJsonPath('ok', false)
            ->assertJsonPath('message', 'Resource is read-only.');
    }
}