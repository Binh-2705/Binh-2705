<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LeaveRequestBizController extends Controller
{
    private function conn(): string
    {
        return (string) config('service_registry.services.hr.connection', config('database.default'));
    }

    public function approve(int $id): JsonResponse
    {
        $connection = DB::connection($this->conn());

        try {
            $result = $connection->transaction(function () use ($connection, $id) {
                $leave = $connection->table('nghiphep')->where('MaNP', $id)->lockForUpdate()->first();
                if ($leave === null) {
                    return ['ok' => false, 'message' => 'Không tìm thấy đơn nghỉ phép.'];
                }

                $status = trim((string) ($leave->TrangThai ?? ''));
                if ($status !== 'Chờ duyệt') {
                    return ['ok' => false, 'message' => 'Đơn nghỉ phép không còn ở trạng thái Chờ duyệt.'];
                }

                $connection->table('nghiphep')->where('MaNP', $id)->update([
                    'TrangThai' => 'Đã duyệt',
                    'NgayDuyet' => now()->toDateString(),
                ]);

                $cursor = strtotime((string) $leave->TuNgay);
                $end = strtotime((string) $leave->DenNgay);
                while ($cursor !== false && $end !== false && $cursor <= $end) {
                    $date = date('Y-m-d', $cursor);
                    $connection->table('chamcong')->updateOrInsert(
                        ['MaNV' => (int) $leave->MaNV, 'Ngay' => $date],
                        ['TrangThai' => 'Nghi phep', 'GioVao' => null, 'GioRa' => null]
                    );
                    $cursor = strtotime('+1 day', $cursor);
                }

                return [
                    'ok' => true,
                    'message' => 'Đã duyệt đơn nghỉ phép.',
                    'state_diff' => 'Trạng thái: ' . $status . ' -> Đã duyệt',
                ];
            });

            return is_array($result) && ($result['ok'] ?? false)
                ? response()->json($result)
                : response()->json($result, 422);
        } catch (\Throwable) {
            return response()->json(['ok' => false, 'message' => 'Không thể duyệt đơn nghỉ phép.'], 500);
        }
    }

    public function reject(int $id): JsonResponse
    {
        $connection = DB::connection($this->conn());

        $leave = $connection->table('nghiphep')->where('MaNP', $id)->first();
        if ($leave === null) {
            return response()->json(['ok' => false, 'message' => 'Không tìm thấy đơn nghỉ phép.'], 404);
        }

        $status = trim((string) ($leave->TrangThai ?? ''));
        if ($status !== 'Chờ duyệt') {
            return response()->json(['ok' => false, 'message' => 'Đơn nghỉ phép không còn ở trạng thái Chờ duyệt.'], 422);
        }

        $connection->table('nghiphep')->where('MaNP', $id)->update([
            'TrangThai' => 'Từ chối',
            'NgayDuyet' => now()->toDateString(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Đã từ chối đơn nghỉ phép.',
            'state_diff' => 'Trạng thái: ' . $status . ' -> Từ chối',
        ]);
    }

    public function create(int $employeeId, string $startDate, string $endDate, string $reason, string $leaveType): JsonResponse
    {
        try {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
        } catch (\Throwable) {
            return response()->json(['ok' => false, 'message' => 'Ngày nghỉ phép không hợp lệ.'], 422);
        }

        if ($end < $start) {
            return response()->json(['ok' => false, 'message' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.'], 422);
        }

        $days = (int) $start->diff($end)->days + 1;
        $newId = DB::connection($this->conn())->table('nghiphep')->insertGetId([
            'MaNV' => $employeeId,
            'TuNgay' => $startDate,
            'DenNgay' => $endDate,
            'SoNgayNghi' => $days,
            'LyDo' => $reason,
            'LoaiNghi' => $leaveType,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Đã tạo đơn nghỉ phép.',
            'id' => $newId,
            'state_diff' => 'Mã đơn mới: #' . $newId . ' | Trạng thái: Chờ duyệt',
        ]);
    }
}
