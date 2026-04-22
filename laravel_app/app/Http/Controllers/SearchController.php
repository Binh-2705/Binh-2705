<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->query('q', ''));
        $results = [];

        if ($keyword !== '') {
            $hr = (string) config('service_registry.services.hr.connection', config('database.default'));
            $recruitment = (string) config('service_registry.services.recruitment.connection', config('database.default'));
            $reporting = (string) config('service_registry.services.reporting.connection', config('database.default'));

            $results = [
                'employees' => DB::connection($hr)->table('nhanvien')->where('HoTen', 'like', "%{$keyword}%")->limit(10)->get(),
                'departments' => DB::connection($hr)->table('phongban')->where('TenPB', 'like', "%{$keyword}%")->limit(10)->get(),
                'positions' => DB::connection($hr)->table('chucvu')->where('TenCV', 'like', "%{$keyword}%")->limit(10)->get(),
                'campaigns' => DB::connection($recruitment)->table('dottuyendung')->where('TenDotTuyenDung', 'like', "%{$keyword}%")->limit(10)->get(),
                'reports' => DB::connection($reporting)->table('baocao')->where('TenBaoCao', 'like', "%{$keyword}%")->limit(10)->get(),
            ];
        }

        return view('search.index', [
            'keyword' => $keyword,
            'results' => $results,
        ]);
    }
}