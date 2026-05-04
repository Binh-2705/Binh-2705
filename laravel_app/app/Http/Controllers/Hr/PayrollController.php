<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;

use App\Jobs\ProcessMonthlyPayrollJob;
use App\Services\InternalApiClient;
use App\Services\PayrollService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __construct(
        private PayrollService $payrollService,
        private InternalApiClient $client,
    ) {}

    public function index(Request $request): View
    {
        $account  = (array) session('taikhoan', []);
        $role     = strtolower(trim((string) ($account['VaiTro'] ?? '')));
        $ownMaNV  = (int) ($account['MaNV'] ?? 0);
        $isSelfView = $role === 'nhanvien' && $ownMaNV > 0;

        $filters = $isSelfView
            ? ['ma_nv' => $ownMaNV]
            : $request->only(['q', 'month', 'year', 'status']);

        $records = $this->payrollService->paginate($filters);
        $records->appends($request->query());

        return view('luong.index', [
            'records'    => $records,
            'filters'    => $isSelfView ? [] : $request->only(['q', 'month', 'year', 'status']),
            'isSelfView' => $isSelfView,
        ]);
    }

    public function create(): View
    {
        return view('luong.form', [
            'mode' => 'create',
            'record' => null,
            'employees' => $this->payrollService->employeeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        try {
            $payrollId = $this->payrollService->create($payload);

            return redirect()->route('luong.edit', ['payroll' => $payrollId])
                ->with('success', 'Da tao bang luong thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the tao bang luong: ' . $exception->getMessage()]);
        }
    }

    public function edit(int $payroll): View
    {
        $item = $this->payrollService->find($payroll);
        abort_if($item === null, 404);

        return view('luong.form', [
            'mode' => 'edit',
            'record' => $item,
            'employees' => $this->payrollService->employeeOptions(),
        ]);
    }

    public function show(int $payroll): View
    {
        $item = $this->payrollService->find($payroll);
        abort_if($item === null, 404);

        return view('luong.show', [
            'record' => $item,
        ]);
    }

    public function update(Request $request, int $payroll): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        try {
            $this->payrollService->update($payroll, $payload);

            return redirect()->route('luong.edit', ['payroll' => $payroll])
                ->with('success', 'Da cap nhat bang luong thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the cap nhat bang luong: ' . $exception->getMessage()]);
        }
    }

    public function runMonthly(Request $request): JsonResponse|RedirectResponse
    {
        $payload = $request->validate([
            'thang' => ['required', 'integer', 'between:1,12'],
            'nam'   => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $month  = (int) $payload['thang'];
        $year   = (int) $payload['nam'];
        $maTK   = (int) session('MaTK', 0);

        // Nếu queue là database/redis → dispatch async
        $isAsync = config('queue.default') !== 'sync';

        if ($isAsync) {
            ProcessMonthlyPayrollJob::dispatch($month, $year, $maTK)->onQueue('payroll');

            if (!$request->expectsJson() && !$request->ajax()) {
                return redirect()->route('luong.index', ['month' => $month, 'year' => $year])
                    ->with('success', "Đã gửi job tính lương tháng {$month}/{$year} vào hàng đợi. Kết quả sẽ sẵn sàng sau ít phút.");
            }

            return response()->json([
                'ok'         => true,
                'queued'     => true,
                'status_url' => route('luong.job-status', ['month' => $month, 'year' => $year]),
                'message'    => "Job tính lương tháng {$month}/{$year} đã được đưa vào hàng đợi.",
            ]);
        }

        // Fallback: sync (chạy ngay, dùng khi QUEUE_CONNECTION=sync)
        try {
            $count = $this->payrollService->processMonthlyPayroll($month, $year);

            if (!$request->expectsJson() && !$request->ajax()) {
                return redirect()->route('luong.index', ['month' => $month, 'year' => $year])
                    ->with('success', "Đã tính lương tháng {$month}/{$year} thành công. Số bảng lương đã xử lý: {$count}.");
            }

            return response()->json([
                'ok'        => true,
                'queued'    => false,
                'processed' => $count,
                'message'   => 'Đã tính lương thành công.',
            ]);
        } catch (\Throwable) {
            if (!$request->expectsJson() && !$request->ajax()) {
                return redirect()->route('luong.index')
                    ->withErrors(['form' => 'Không thể tính lương. Vui lòng kiểm tra dữ liệu chấm công.']);
            }

            return response()->json([
                'ok'      => false,
                'message' => 'Không thể tính lương. Vui lòng kiểm tra dữ liệu chấm công.',
            ], 422);
        }
    }

    public function jobStatus(Request $request): JsonResponse
    {
        $month     = (int) $request->input('month', date('n'));
        $year      = (int) $request->input('year', date('Y'));
        $cacheKey  = "payroll_job_status_{$month}_{$year}";
        $status    = Cache::get($cacheKey);

        if ($status === null) {
            return response()->json(['ok' => true, 'status' => 'not_started', 'month' => $month, 'year' => $year]);
        }

        return response()->json(['ok' => true] + (array) $status);
    }


    public function exportExcel(Request $request): StreamedResponse
    {
        $rows = $this->client->get('biz/payroll/export', $request->only(['month', 'year', 'thang', 'nam']))['data'] ?? [];

        return response()->streamDownload(function () use ($rows) {
            echo "\xEF\xBB\xBF";
            echo "<table border='1'><tr><th>Mã BL</th><th>Mã NV</th><th>Nhân viên</th><th>Tháng</th><th>Năm</th><th>Tổng lương</th><th>Trạng thái</th></tr>";
            foreach ($rows as $row) {
                $row = (array) $row;
                echo '<tr>';
                echo '<td>' . e((string) ($row['MaBL'] ?? '')) . '</td>';
                echo '<td>' . e((string) ($row['MaNV'] ?? '')) . '</td>';
                echo '<td>' . e((string) ($row['HoTen'] ?? '')) . '</td>';
                echo '<td>' . e((string) ($row['Thang'] ?? '')) . '</td>';
                echo '<td>' . e((string) ($row['Nam'] ?? '')) . '</td>';
                echo '<td>' . e((string) ($row['TongLuong'] ?? '')) . '</td>';
                echo '<td>' . e((string) ($row['TrangThai'] ?? '')) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }, 'bang_luong_' . now()->format('Ymd-His') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function lock(int $payroll): RedirectResponse
    {
        $this->client->post("biz/payroll/{$payroll}/lock");
        return redirect()->route('luong.index')->with('success', 'Đã chốt lương thành công.');
    }

    public function unlock(int $payroll): RedirectResponse
    {
        $this->client->post("biz/payroll/{$payroll}/unlock");
        return redirect()->route('luong.index')->with('success', 'Đã mở chốt lương thành công.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'MaNV' => ['required', 'integer'],
            'Thang' => ['required', 'integer', 'between:1,12'],
            'Nam' => ['required', 'integer', 'min:2000', 'max:2100'],
            'LuongCoSo' => ['nullable', 'numeric'],
            'HeSoLuong' => ['nullable', 'numeric'],
            'HeSoChucVu' => ['nullable', 'numeric'],
            'PhuCap' => ['nullable', 'numeric'],
            'Thuong' => ['nullable', 'numeric'],
            'Phat' => ['nullable', 'numeric'],
            'BaoHiem' => ['nullable', 'numeric'],
            'TongLuong' => ['nullable', 'numeric'],
            'TrangThai' => ['required', 'string', 'max:20'],
            'NgayTinh' => ['nullable', 'date'],
        ]);
    }
}