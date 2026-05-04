<?php

namespace Tests\Feature;

use App\Services\DepartmentDirectoryService;
use App\Services\HrEmployeeService;
use App\Services\PermissionService;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class HrModulesTest extends TestCase
{
    public function test_employee_index_requires_login(): void
    {
        $response = $this->get('/nhanvien');

        $response->assertRedirect(route('login.form'));
    }

    public function test_employee_index_renders_for_authorized_session(): void
    {
        $this->mock(PermissionService::class, function ($mock) {
            $mock->shouldReceive('hasPermission', 'hasPermissionFromCache')->andReturnTrue();
        });

        $this->mock(HrEmployeeService::class, function ($mock) {
            $mock->shouldReceive('paginate')->andReturn(new LengthAwarePaginator([
                (object) [
                    'MaNV' => 1,
                    'HoTen' => 'Nguyen Van A',
                    'Email' => 'a@example.com',
                    'TenPB' => 'Nhan Su',
                    'TenCV' => 'Nhan vien',
                    'TenBac' => 'Bac 1',
                    'TrangThai' => 'Đang làm',
                ],
            ], 1, 12));
            $mock->shouldReceive('options')->andReturn([
                'departments' => collect([(object) ['MaPB' => 1, 'TenPB' => 'Nhan Su']]),
                'positions' => collect(),
                'salaryGrades' => collect(),
            ]);
        });

        $response = $this->withSession(['MaTK' => 3])->get('/nhanvien');

        $response->assertOk()->assertSee('Nguyen Van A');
    }

    public function test_department_index_renders_for_authorized_session(): void
    {
        $this->mock(PermissionService::class, function ($mock) {
            $mock->shouldReceive('hasPermission', 'hasPermissionFromCache')->andReturnTrue();
        });

        $this->mock(DepartmentDirectoryService::class, function ($mock) {
            $mock->shouldReceive('paginate')->andReturn(new LengthAwarePaginator([
                (object) [
                    'MaPB' => 1,
                    'TenPB' => 'Nhan Su',
                    'MoTa' => 'Phong ban nhan su',
                    'SoNhanVien' => 5,
                ],
            ], 1, 12));
        });

        $response = $this->withSession(['MaTK' => 3])->get('/phongban');

        $response->assertOk()->assertSee('Nhan Su');
    }
}