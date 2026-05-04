<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceBizController extends Controller
{
    private function conn(): string
    {
        return (string) config('service_registry.services.attendance.connection', config('database.default'));
    }

    private function baseQuery(): Builder
    {
        return DB::connection($this->conn())
            ->table('chamcong as cc')
            ->join('nhanvien as nv', 'nv.MaNV', '=', 'cc.MaNV')
            ->leftJoin('hosonhanvien as hs', 'hs.MaNV', '=', 'nv.MaNV')
            ->leftJoin('phongban as pb', 'pb.MaPB', '=', 'hs.MaPB')
            ->select(['cc.MaCC', 'cc.MaNV', 'cc.Ngay', 'cc.GioVao', 'cc.GioRa', 'cc.TrangThai', 'cc.GhiChu', 'nv.HoTen', 'pb.TenPB']);
    }

    public function paginate(Request $request): JsonResponse
    {
        $filters = (array) $request->input('filters', []);
        $perPage = max(1, min((int) $request->input('perPage', 15), 100));
        $page    = max(1, (int) $request->input('page', 1));

        $query = $this->baseQuery()
            ->when(!empty($filters['ma_nv']), fn (Builder $q) => $q->where('cc.MaNV', (int) $filters['ma_nv']))
            ->when(!empty($filters['q']), function (Builder $q) use ($filters) {
                $kw = trim((string) $filters['q']);
                $q->where(fn (Builder $i) => $i->where('nv.HoTen', 'like', "%{$kw}%")->orWhere('cc.MaNV', 'like', "%{$kw}%"));
            })
            ->when(!empty($filters['status']), fn (Builder $q) => $q->where('cc.TrangThai', $filters['status']))
            ->when(!empty($filters['date']), fn (Builder $q) => $q->whereDate('cc.Ngay', $filters['date']))
            ->orderByDesc('cc.Ngay')
            ->orderBy('cc.MaNV');

        $total = (clone $query)->count();
        $data  = $query->forPage($page, $perPage)->get()->map(fn ($r) => (array) $r)->all();

        return response()->json(['ok' => true, 'data' => $data, 'total' => $total, 'per_page' => $perPage, 'current_page' => $page]);
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->baseQuery()->where('cc.MaCC', $id)->first();
        if (!$item) {
            return response()->json(['ok' => false, 'message' => 'Cham cong khong ton tai.'], 404);
        }
        return response()->json(['ok' => true, 'data' => (array) $item]);
    }

    public function employeeOptions(): JsonResponse
    {
        $options = DB::connection($this->conn())->table('nhanvien')->orderBy('HoTen')->get(['MaNV', 'HoTen']);
        return response()->json(['ok' => true, 'data' => $options]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = (array) $request->json()->all();
        $id = (int) DB::connection($this->conn())->table('chamcong')->insertGetId($payload, 'MaCC');
        return response()->json(['ok' => true, 'id' => $id], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payload = (array) $request->json()->all();
        DB::connection($this->conn())->table('chamcong')->where('MaCC', $id)->update($payload);
        return response()->json(['ok' => true]);
    }

    public function destroy(int $id): JsonResponse
    {
        DB::connection($this->conn())->table('chamcong')->where('MaCC', $id)->delete();
        return response()->json(['ok' => true]);
    }

    public function workedDays(Request $request): JsonResponse
    {
        $employeeId = (int) $request->query('employee_id', 0);
        $month      = (int) $request->query('month', date('n'));
        $year       = (int) $request->query('year', date('Y'));

        $summary = DB::connection($this->conn())
            ->table('v_tonghopcong')
            ->where('MaNV', $employeeId)
            ->where('Thang', $month)
            ->where('Nam', $year)
            ->first(['SoNgayCong', 'GioOT']);

        return response()->json([
            'ok'         => true,
            'SoNgayLam'  => (float) ($summary->SoNgayCong ?? 0),
            'GioOT'      => (float) ($summary->GioOT ?? 0),
            'Thang'      => $month,
            'Nam'        => $year,
        ]);
    }

    public function exportRows(Request $request): JsonResponse
    {
        $filters = (array) $request->input('filters', []);
        $rows = $this->baseQuery()
            ->when(!empty($filters['q']), function (Builder $q) use ($filters) {
                $kw = trim((string) $filters['q']);
                $q->where(fn (Builder $i) => $i->where('nv.HoTen', 'like', "%{$kw}%")->orWhere('cc.MaNV', 'like', "%{$kw}%"));
            })
            ->when(!empty($filters['month']), function (Builder $q) use ($filters) {
                $q->whereMonth('cc.Ngay', (int) $filters['month']);
            })
            ->when(!empty($filters['year']), function (Builder $q) use ($filters) {
                $q->whereYear('cc.Ngay', (int) $filters['year']);
            })
            ->orderByDesc('cc.Ngay')
            ->get()->map(fn ($r) => (array) $r)->all();

        return response()->json(['ok' => true, 'data' => $rows]);
    }

    public function monthlyMatrix(Request $request): JsonResponse
    {
        $month = max(1, min(12, (int) $request->query('month', now()->month)));
        $year  = max(2000, min(2100, (int) $request->query('year', now()->year)));
        $maNV  = $request->query('ma_nv') !== null ? (int) $request->query('ma_nv') : null;

        $conn = DB::connection($this->conn());

        // Get all employees with department (optionally filtered to a single employee)
        $employees = $conn->table('nhanvien as nv')
            ->leftJoin('hosonhanvien as hs', 'hs.MaNV', '=', 'nv.MaNV')
            ->leftJoin('phongban as pb', 'pb.MaPB', '=', 'hs.MaPB')
            ->select(['nv.MaNV', 'nv.HoTen', 'pb.TenPB'])
            ->when($maNV !== null, fn ($q) => $q->where('nv.MaNV', $maNV))
            ->orderBy('pb.TenPB')
            ->orderBy('nv.HoTen')
            ->get();

        // Get attendance records for the month
        $records = $conn->table('chamcong')
            ->whereMonth('Ngay', $month)
            ->whereYear('Ngay', $year)
            ->get()
            ->groupBy('MaNV');

        $matrix = [];
        foreach ($employees as $emp) {
            $dept = (string) ($emp->TenPB ?? 'Chưa phân công');
            $empRecords = $records->get((string) $emp->MaNV, collect());
            $days = [];
            foreach ($empRecords as $rec) {
                $day = date('d', strtotime((string) $rec->Ngay));
                $days[$day] = ['s' => (string) $rec->TrangThai, 'id' => (int) $rec->MaCC];
            }
            $matrix[$dept][] = [
                'MaNV'  => (int) $emp->MaNV,
                'HoTen' => (string) $emp->HoTen,
                'Ngay'  => $days,
            ];
        }

        return response()->json(['ok' => true, 'data' => $matrix]);
    }
}
