<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;

use App\Services\AttendanceService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService)
    {
    }

    public function index(Request $request): View
    {
        $account  = (array) session('taikhoan', []);
        $role     = strtolower(trim((string) ($account['VaiTro'] ?? '')));
        $ownMaNV  = (int) ($account['MaNV'] ?? 0);
        $isSelfView = $role === 'nhanvien' && $ownMaNV > 0;

        $filters = $isSelfView
            ? ['ma_nv' => $ownMaNV]
            : $request->only(['q', 'status', 'date']);

        $records = $this->attendanceService->paginate($filters);
        $records->appends($request->query());

        return view('chamcong.index', [
            'records'    => $records,
            'filters'    => $isSelfView ? [] : $request->only(['q', 'status', 'date']),
            'isSelfView' => $isSelfView,
        ]);
    }

    public function create(): View
    {
        return view('chamcong.form', [
            'mode' => 'create',
            'record' => null,
            'employees' => $this->attendanceService->employeeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        try {
            $attendanceId = $this->attendanceService->create($payload);

            return redirect()->route('chamcong.edit', ['attendance' => $attendanceId])
                ->with('success', 'Da tao cham cong thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the tao cham cong: ' . $exception->getMessage()]);
        }
    }

    public function edit(int $attendance): View
    {
        $item = $this->attendanceService->find($attendance);
        abort_if($item === null, 404);

        return view('chamcong.form', [
            'mode' => 'edit',
            'record' => $item,
            'employees' => $this->attendanceService->employeeOptions(),
        ]);
    }

    public function update(Request $request, int $attendance): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        try {
            $this->attendanceService->update($attendance, $payload);

            return redirect()->route('chamcong.edit', ['attendance' => $attendance])
                ->with('success', 'Da cap nhat cham cong thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the cap nhat cham cong: ' . $exception->getMessage()]);
        }
    }

    public function destroy(int $attendance): RedirectResponse
    {
        try {
            $this->attendanceService->delete($attendance);

            return redirect()->route('chamcong.index')
                ->with('success', 'Da xoa cham cong thanh cong.');
        } catch (QueryException $exception) {
            return back()->withErrors(['form' => 'Khong the xoa cham cong.']);
        }
    }

    public function destroyLegacy(int $attendance): RedirectResponse
    {
        return $this->destroy($attendance);
    }

    public function workedDays(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'manv' => ['required', 'integer'],
            'thang' => ['required', 'integer', 'between:1,12'],
            'nam' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        return response()->json(
            $this->attendanceService->workedDaysByMonth(
                (int) $payload['manv'],
                (int) $payload['thang'],
                isset($payload['nam']) ? (int) $payload['nam'] : null,
            )
        );
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $payload = $request->validate([
            'thang' => ['required', 'integer', 'between:1,12'],
            'nam' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $month = (int) $payload['thang'];
        $year = (int) $payload['nam'];
        $matrix = $this->attendanceService->monthlyAttendanceMatrix($month, $year);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        return response()->streamDownload(function () use ($matrix, $daysInMonth) {
            echo "<table border='1'><tr><th>Ma NV</th><th>Ho Ten</th>";
            for ($day = 1; $day <= $daysInMonth; $day++) {
                echo '<th>' . $day . '</th>';
            }
            echo '</tr>';

            foreach ($matrix as $departmentRows) {
                foreach ($departmentRows as $employee) {
                    echo '<tr>';
                    echo '<td>' . e((string) $employee['MaNV']) . '</td>';
                    echo '<td>' . e((string) $employee['HoTen']) . '</td>';
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $value = $employee['Ngay'][str_pad((string) $day, 2, '0', STR_PAD_LEFT)] ?? '';
                        echo '<td>' . e((string) $value) . '</td>';
                    }
                    echo '</tr>';
                }
            }

            echo '</table>';
        }, "ChamCong_{$month}_{$year}.xls", [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function updateCell(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'MaCC'      => ['nullable', 'integer', 'min:0'],
                'MaNV'      => ['required', 'integer'],
                'Ngay'      => ['required', 'date'],
                'TrangThai' => ['nullable', 'string', 'in:Di lam,Nghi phep,Nghi khong luong,Cong tac,Le,Di muon'],
            ]);

            $maCC = (int) ($data['MaCC'] ?? 0);
            $status = $data['TrangThai'] ?? '';

            if ($status === '') {
                if ($maCC > 0) {
                    $this->attendanceService->delete($maCC);
                }

                return response()->json(['ok' => true, 'MaCC' => 0]);
            }

            if ($maCC > 0) {
                $this->attendanceService->update($maCC, ['TrangThai' => $status]);

                return response()->json(['ok' => true, 'MaCC' => $maCC]);
            }

            $newId = $this->attendanceService->create([
                'MaNV'      => $data['MaNV'],
                'Ngay'      => $data['Ngay'],
                'TrangThai' => $status,
            ]);

            return response()->json(['ok' => true, 'MaCC' => $newId]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Không thể lưu chấm công: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function matrix(Request $request): View
    {
        $month = max(1, min(12, (int) $request->query('thang', now()->month)));
        $year  = max(2000, min(2100, (int) $request->query('nam', now()->year)));

        $account    = (array) session('taikhoan', []);
        $role       = strtolower(trim((string) ($account['VaiTro'] ?? '')));
        $ownMaNV    = (int) ($account['MaNV'] ?? 0);
        $isSelfView = $role === 'nhanvien' && $ownMaNV > 0;

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Build days array: [1 => 0 (Sun), 2 => 1 (Mon), ...]
        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $days[$d] = (int) \Carbon\Carbon::createFromDate($year, $month, $d)->dayOfWeek;
        }

        $matrix = $this->attendanceService->monthlyAttendanceMatrix($month, $year, $isSelfView ? $ownMaNV : null);

        return view('chamcong.matrix', compact('month', 'year', 'days', 'matrix', 'isSelfView'));
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'MaNV' => ['required', 'integer'],
            'Ngay' => ['required', 'date'],
            'GioVao' => ['nullable', 'date_format:H:i'],
            'GioRa' => ['nullable', 'date_format:H:i'],
            'TrangThai' => ['required', 'in:Di lam,Nghi phep,Nghi khong luong,Cong tac,Le,Di muon'],
            'GhiChu' => ['nullable', 'string', 'max:255'],
        ]);
    }
}