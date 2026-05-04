<?php

namespace Tests\Feature;

use App\Services\AttendanceService;
use App\Services\GenericResourceModuleService;
use App\Services\PermissionService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class RemainingExportCompatibilityTest extends TestCase
{
    public function test_attendance_export_excel_streams_monthly_matrix(): void
    {
        $this->mock(PermissionService::class, fn ($mock) => $mock->shouldReceive('hasPermission', 'hasPermissionFromCache')->andReturnTrue());
        $this->mock(AttendanceService::class, function ($mock) {
            $mock->shouldReceive('monthlyAttendanceMatrix')->once()->with(4, 2026)->andReturn([
                'IT' => [
                    ['MaNV' => 7, 'HoTen' => 'Nguyen Van A', 'Ngay' => ['01' => 'X'], 'TongCong' => 1],
                ],
            ]);
        });

        $response = $this->withSession(['MaTK' => 3])->get('/chamcong/export-excel?thang=4&nam=2026');
        $response->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', (string) $response->headers->get('content-type'));
    }

    public function test_generic_module_export_excel_streams_rows(): void
    {
        $this->mock(PermissionService::class, fn ($mock) => $mock->shouldReceive('hasPermission', 'hasPermissionFromCache')->andReturnTrue());
        $this->mock(GenericResourceModuleService::class, function ($mock) {
            $mock->shouldReceive('exportRows')->once()->with('positions', [])->andReturn([
                'meta' => [
                    'module' => config('laravel_resource_modules.positions'),
                    'resource' => ['columns' => []],
                ],
                'columns' => ['MaCV', 'TenCV'],
                'rows' => new Collection([
                    ['MaCV' => 1, 'TenCV' => 'Truong phong'],
                ]),
            ]);
        });

        $response = $this->withSession(['MaTK' => 3])->get('/positions/export-excel');
        $response->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', (string) $response->headers->get('content-type'));
    }
}