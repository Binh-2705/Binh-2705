<?php

namespace App\Http\Controllers;

use App\Services\HrEmployeeService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(private HrEmployeeService $employeeService)
    {
    }

    public function index(Request $request): View
    {
        $employees = $this->employeeService->paginate($request->only(['q', 'status', 'department']));
        $employees->appends($request->query());

        return view('nhanvien.index', [
            'employees' => $employees,
            'filters' => $request->only(['q', 'status', 'department']),
            'options' => $this->employeeService->options(),
        ]);
    }

    public function create(): View
    {
        return view('nhanvien.form', [
            'mode' => 'create',
            'employee' => null,
            'options' => $this->employeeService->options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        try {
            $employeeId = $this->employeeService->create($payload);

            return redirect()->route('nhanvien.edit', ['employee' => $employeeId])
                ->with('success', 'Da tao nhan vien thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the tao nhan vien: ' . $exception->getMessage()]);
        }
    }

    public function edit(int $employee): View
    {
        $item = $this->employeeService->find($employee);
        abort_if($item === null, 404);

        return view('nhanvien.form', [
            'mode' => 'edit',
            'employee' => $item,
            'options' => $this->employeeService->options(),
        ]);
    }

    public function update(Request $request, int $employee): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        try {
            $this->employeeService->update($employee, $payload);

            return redirect()->route('nhanvien.edit', ['employee' => $employee])
                ->with('success', 'Da cap nhat nhan vien thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the cap nhat nhan vien: ' . $exception->getMessage()]);
        }
    }

    public function destroy(int $employee): RedirectResponse
    {
        try {
            $this->employeeService->delete($employee);

            return redirect()->route('nhanvien.index')
                ->with('success', 'Da xoa nhan vien thanh cong.');
        } catch (QueryException $exception) {
            return back()->withErrors(['form' => 'Khong the xoa nhan vien do con du lieu lien quan.']);
        }
    }

    public function destroyLegacy(int $employee): RedirectResponse
    {
        return $this->destroy($employee);
    }

    public function salaryGradesByBand(Request $request): Response
    {
        $bandId = $request->query('ma_ngach');
        $grades = $bandId !== null && $bandId !== ''
            ? $this->employeeService->salaryGradesByBand($bandId)
            : collect();

        $html = '<option value="">-- Chọn bậc lương --</option>';

        if ($grades->isEmpty()) {
            $html .= '<option value="">Chưa có bậc lương cho ngạch này</option>';
        } else {
            foreach ($grades as $grade) {
                $html .= '<option value="' . e((string) $grade->MaBac) . '">'
                    . e((string) $grade->TenBac) . ' (HS: ' . e((string) $grade->HeSoLuong) . ')'
                    . '</option>';
            }
        }

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'HoTen' => ['required', 'string', 'max:100'],
            'GioiTinh' => ['nullable', 'in:Nam,Nữ'],
            'NgaySinh' => ['nullable', 'date'],
            'Email' => ['nullable', 'email', 'max:100'],
            'DienThoai' => ['nullable', 'string', 'max:20'],
            'TrangThai' => ['required', 'in:Đang làm,Nghỉ'],
            'MaBac' => ['nullable', 'integer'],
            'MaPB' => ['nullable', 'integer'],
            'MaCV' => ['nullable', 'integer'],
            'NgayVaoLam' => ['nullable', 'date'],
            'DiaChi' => ['nullable', 'string'],
        ]);
    }
}