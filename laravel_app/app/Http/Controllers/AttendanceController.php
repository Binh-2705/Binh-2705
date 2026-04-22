<?php

namespace App\Http\Controllers;

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
        $records = $this->attendanceService->paginate($request->only(['q', 'status', 'date']));
        $records->appends($request->query());

        return view('chamcong.index', [
            'records' => $records,
            'filters' => $request->only(['q', 'status', 'date']),
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

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'MaNV' => ['required', 'integer'],
            'Ngay' => ['required', 'date'],
            'GioVao' => ['nullable', 'date_format:H:i'],
            'GioRa' => ['nullable', 'date_format:H:i'],
            'TrangThai' => ['required', 'in:Di lam,Nghi phep,Nghi khong luong,Cong tac,Le'],
            'GhiChu' => ['nullable', 'string', 'max:255'],
        ]);
    }
}