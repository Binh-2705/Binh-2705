<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchBizController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->query('q', ''));

        if ($keyword === '') {
            return response()->json(['ok' => true, 'results' => []]);
        }

        $like     = '%' . $keyword . '%';
        $hr       = (string) config('service_registry.services.hr.connection', config('database.default'));
        $recruit  = (string) config('service_registry.services.recruitment.connection', config('database.default'));
        $report   = (string) config('service_registry.services.reporting.connection', config('database.default'));

        $results = [
            'employees'   => DB::connection($hr)->table('nhanvien')->where('HoTen', 'like', $like)->limit(10)->get()->map(fn ($r) => (array) $r)->all(),
            'departments' => DB::connection($hr)->table('phongban')->where('TenPB', 'like', $like)->limit(10)->get()->map(fn ($r) => (array) $r)->all(),
            'positions'   => DB::connection($hr)->table('chucvu')->where('TenCV', 'like', $like)->limit(10)->get()->map(fn ($r) => (array) $r)->all(),
            'campaigns'   => DB::connection($recruit)->table('dottuyendung')->where('TenDotTuyenDung', 'like', $like)->limit(10)->get()->map(fn ($r) => (array) $r)->all(),
            'reports'     => DB::connection($report)->table('baocao')->where('TenBaoCao', 'like', $like)->limit(10)->get()->map(fn ($r) => (array) $r)->all(),
        ];

        return response()->json(['ok' => true, 'keyword' => $keyword, 'results' => $results]);
    }
}
