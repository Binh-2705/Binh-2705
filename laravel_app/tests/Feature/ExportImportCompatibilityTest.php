<?php

namespace Tests\Feature;

use App\Services\AppAuditLogService;
use App\Services\DepartmentDirectoryService;
use App\Services\PermissionService;
use App\Services\ReportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ExportImportCompatibilityTest extends TestCase
{
    public function test_department_export_excel_streams_laravel_response(): void
    {
        $this->mock(PermissionService::class, fn ($mock) => $mock->shouldReceive('hasPermission', 'hasPermissionFromCache')->andReturnTrue());
        $this->mock(DepartmentDirectoryService::class, function ($mock) {
            $mock->shouldReceive('exportRows')->once()->with([])->andReturn([
                ['MaPB' => 1, 'TenPB' => 'IT', 'MoTa' => 'Phong cong nghe'],
            ]);
        });

        $response = $this->withSession(['MaTK' => 3])->get('/phongban/export-excel');
        $response->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', (string) $response->headers->get('content-type'));
    }

    public function test_department_import_csv_redirects_with_success(): void
    {
        $this->mock(PermissionService::class, fn ($mock) => $mock->shouldReceive('hasPermission', 'hasPermissionFromCache')->andReturnTrue());
        $this->mock(DepartmentDirectoryService::class, function ($mock) {
            $mock->shouldReceive('importRows')->once()->andReturn(2);
        });

        $file = UploadedFile::fake()->createWithContent('departments.csv', "TenPB,MoTa\nIT,Mo ta 1\nHR,Mo ta 2\n");

        $this->withSession(['MaTK' => 3])
            ->post('/phongban/import-csv', ['filecsv' => $file])
            ->assertRedirect('/phongban');
    }

    public function test_report_export_json_returns_payload(): void
    {
        $this->mock(PermissionService::class, fn ($mock) => $mock->shouldReceive('hasPermission', 'hasPermissionFromCache')->andReturnTrue());
        $this->mock(ReportService::class, function ($mock) {
            $mock->shouldReceive('exportRows')->once()->with([])->andReturn([
                ['MaBC' => 5, 'TenBaoCao' => 'Tong hop', 'LoaiBaoCao' => 'Nhân sự', 'NguoiTao' => 'admin'],
            ]);
        });

        $this->withSession(['MaTK' => 3])
            ->get('/baocao/export-json')
            ->assertOk()
            ->assertSee('Tong hop');
    }

    public function test_audit_log_export_json_returns_filtered_logs(): void
    {
        $this->mock(PermissionService::class, fn ($mock) => $mock->shouldReceive('hasPermission', 'hasPermissionFromCache')->andReturnTrue());
        $this->mock(AppAuditLogService::class, function ($mock) {
            $mock->shouldReceive('readFilteredRows')->once()->with('ERROR', 'csrf')->andReturn([
                ['time' => '2026-04-09 10:00:00', 'level' => 'ERROR', 'message' => 'CSRF mismatch', 'context' => '{}'],
            ]);
        });

        $this->withSession(['MaTK' => 3])
            ->get('/audit-log/export-json?level=ERROR&q=csrf')
            ->assertOk()
            ->assertSee('CSRF mismatch');
    }
}