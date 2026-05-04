<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;

use App\Services\GenericResourceModuleService;
use App\Services\InternalApiClient;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;
use LogicException;

class ResourceModuleController extends Controller
{
    public function __construct(
        private GenericResourceModuleService $modules,
        private InternalApiClient $client,
    ) {}

    public function index(Request $request, string $module): View
    {
        $meta = $this->modules->describe($module);

        $filters = $request->only(['q']);

        // NhanVien can only see their own records for personal modules
        if (in_array($module, ['employee-profiles', 'assignments', 'contracts', 'leave-requests', 'insurances'], true)) {
            $account = (array) session('taikhoan', []);
            $role = strtolower(trim((string) ($account['VaiTro'] ?? '')));
            $ownMaNV = (int) ($account['MaNV'] ?? 0);
            if ($role === 'nhanvien' && $ownMaNV > 0) {
                $filters['ma_nv'] = $ownMaNV;
            }
        }

        $items = $this->modules->paginate($module, $filters);
        $items->appends($request->query());

        return view($this->resolveModuleView($module, 'index'), [
            'moduleKey' => $module,
            'routeKey' => $meta['module']['legacy_name'] ?? $module,
            'moduleConfig' => $meta['module'],
            'resourceConfig' => $meta['resource'],
            'items' => $items,
            'filters' => $request->only(['q']),
            'isSelfView' => isset($filters['ma_nv']),
        ]);
    }

    public function create(string $module): View
    {
        $meta = $this->modules->describe($module);
        abort_if($meta['resource']['read_only'] ?? false, 404);

        // Auto-fill MaNV for NhanVien role
        $defaultRecord = [];
        if (!empty($meta['module']['auto_fill_ma_nv'])) {
            $account = (array) session('taikhoan', []);
            $role = strtolower(trim((string) ($account['VaiTro'] ?? '')));
            if ($role === 'nhanvien' && ($account['MaNV'] ?? 0) > 0) {
                $defaultRecord['MaNV'] = (int) $account['MaNV'];
            }
        }

        return view($this->resolveModuleView($module, 'form'), [
            'mode' => 'create',
            'moduleKey' => $module,
            'routeKey' => $meta['module']['legacy_name'] ?? $module,
            'moduleConfig' => $meta['module'],
            'resourceConfig' => $meta['resource'],
            'record' => $defaultRecord,
            'recordId' => null,
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        $meta = $this->modules->describe($module);
        abort_if($meta['resource']['read_only'] ?? false, 404);
        $routeKey = $meta['module']['legacy_name'] ?? $module;

        try {
            $payload = $this->buildPayload($request, $meta['resource'], $meta['module'], false);
            $recordId = $this->modules->create($module, $payload);

            if ($module === 'employee-profiles') {
                $this->syncEmployeeProfileToRelatedModules($payload);
            }

            return redirect()->route("{$routeKey}.edit", ['record' => $recordId])
                ->with('success', 'Da tao ban ghi thanh cong.');
        } catch (QueryException|LogicException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the tao ban ghi: ' . $exception->getMessage()]);
        }
    }

    public function edit(string $module, string $record): View
    {
        $meta = $this->modules->describe($module);
        abort_if($meta['resource']['read_only'] ?? false, 404);
        $item = $this->modules->find($module, $record);
        abort_if($item === null, 404);

        return view($this->resolveModuleView($module, 'form'), [
            'mode' => 'edit',
            'moduleKey' => $module,
            'routeKey' => $meta['module']['legacy_name'] ?? $module,
            'moduleConfig' => $meta['module'],
            'resourceConfig' => $meta['resource'],
            'record' => $item,
            'recordId' => $record,
        ]);
    }

    public function update(Request $request, string $module, string $record): RedirectResponse
    {
        $meta = $this->modules->describe($module);
        abort_if($meta['resource']['read_only'] ?? false, 404);
        $routeKey = $meta['module']['legacy_name'] ?? $module;

        try {
            $payload = $this->buildPayload($request, $meta['resource'], $meta['module'], true);
            $this->modules->update($module, $record, $payload);

            if ($module === 'employee-profiles') {
                $current = $this->modules->find($module, $record) ?? [];
                $this->syncEmployeeProfileToRelatedModules(array_merge($current, $payload));
            }

            return redirect()->route("{$routeKey}.edit", ['record' => $record])
                ->with('success', 'Da cap nhat ban ghi thanh cong.');
        } catch (QueryException|LogicException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the cap nhat ban ghi: ' . $exception->getMessage()]);
        }
    }

    public function destroy(string $module, string $record): RedirectResponse
    {
        $meta = $this->modules->describe($module);
        abort_if($meta['resource']['read_only'] ?? false, 404);
        $routeKey = $meta['module']['legacy_name'] ?? $module;

        try {
            $this->modules->delete($module, $record);

            return redirect()->route("{$routeKey}.index")
                ->with('success', 'Da xoa ban ghi thanh cong.');
        } catch (QueryException|LogicException $exception) {
            return back()->withErrors(['form' => 'Khong the xoa ban ghi: ' . $exception->getMessage()]);
        }
    }

    public function destroyLegacy(string $module, string $record): RedirectResponse
    {
        return $this->destroy($module, $record);
    }

    public function assignmentHistory(Request $request): RedirectResponse
    {
        $employeeCode = trim((string) $request->query('manv', $request->query('MaNV', '')));

        return redirect()->route('phancong.index', $employeeCode !== '' ? ['q' => $employeeCode] : []);
    }

    public function deactivateInsurance(string $module, string $record): RedirectResponse
    {
        abort_unless($module === 'insurances', 404);

        $this->client->post("biz/insurances/{$record}/deactivate");

        return redirect()->route('baohiem.index')->with('success', 'Da ngung bao hiem thanh cong.');
    }

    public function approveLeaveRequest(string $module, string $record): RedirectResponse
    {
        abort_unless($module === 'leave-requests', 404);

        $result = $this->client->post("biz/leave-requests/{$record}/approve");
        $ok = (bool) ($result['ok'] ?? false);

        return redirect()->route('nghiphep.index')->with(
            $ok ? 'success' : 'error',
            $ok ? 'Da duyet don nghi phep thanh cong.' : (string) ($result['message'] ?? 'Khong the duyet don nghi phep.')
        );
    }

    public function rejectLeaveRequest(string $module, string $record): RedirectResponse
    {
        abort_unless($module === 'leave-requests', 404);

        $this->client->post("biz/leave-requests/{$record}/reject");

        return redirect()->route('nghiphep.index')->with('success', 'Da tu choi don nghi phep thanh cong.');
    }

    public function exportExcel(Request $request, string $module): StreamedResponse
    {
        $meta = $this->modules->describe($module);
        abort_if((bool) ($meta['module']['disable_export'] ?? false), 404);

        $export = $this->modules->exportRows($module, $request->only(['q']));

        return response()->streamDownload(function () use ($export) {
            echo "\xEF\xBB\xBF";
            echo '<table border="1"><tr>';
            foreach ($export['columns'] as $column) {
                echo '<th>' . e((string) $column) . '</th>';
            }
            echo '</tr>';

            foreach ($export['rows'] as $row) {
                echo '<tr>';
                foreach ($export['columns'] as $column) {
                    echo '<td>' . e((string) data_get($row, $column, '')) . '</td>';
                }
                echo '</tr>';
            }

            echo '</table>';
        }, 'du-lieu-' . Str::slug((string) ($export['meta']['module']['title'] ?? $module), '-') . '-' . now()->format('Ymd-His') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    private function buildPayload(Request $request, array $resourceConfig, array $moduleConfig, bool $isUpdate): array
    {
        $payload = [];
        $primaryKeys = is_array($resourceConfig['primary_key'] ?? null)
            ? $resourceConfig['primary_key']
            : [(string) ($resourceConfig['primary_key'] ?? 'id')];
        $fileFields = (array) ($moduleConfig['file_fields'] ?? []);

        // For NhanVien role with auto_fill_ma_nv, force MaNV from session on create
        $forceMaNV = null;
        if (!$isUpdate && !empty($moduleConfig['auto_fill_ma_nv'])) {
            $account = (array) session('taikhoan', []);
            $role = strtolower(trim((string) ($account['VaiTro'] ?? '')));
            if ($role === 'nhanvien' && ($account['MaNV'] ?? 0) > 0) {
                $forceMaNV = (int) $account['MaNV'];
            }
        }

        foreach ($resourceConfig['columns'] as $column) {
            $field = (string) ($column['field'] ?? '');
            $isAutoIncrement = str_contains((string) ($column['extra'] ?? ''), 'auto_increment');

            if ($field === '' || ($isUpdate && in_array($field, $primaryKeys, true)) || $isAutoIncrement) {
                continue;
            }

            // Handle file upload fields
            if (in_array($field, $fileFields, true)) {
                if ($request->hasFile($field) && $request->file($field)->isValid()) {
                    $file = $request->file($field);
                    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                    $uploadDir = base_path('../uploads/photos');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $file->move($uploadDir, $filename);
                    $payload[$field] = $filename;
                }
                // If no new file uploaded, skip (keep existing value)
                continue;
            }

            if (!$request->has($field)) {
                continue;
            }

            $value = $request->input($field);
            $payload[$field] = $value === '' ? null : $value;
        }

        // Enforce MaNV from session (cannot be tampered by form input)
        if ($forceMaNV !== null) {
            $payload['MaNV'] = $forceMaNV;
        }

        return $payload;
    }

    private function syncEmployeeProfileToRelatedModules(array $profileData): void
    {
        $maNV = (int) ($profileData['MaNV'] ?? 0);
        if ($maNV <= 0) {
            return;
        }

        $connection = (string) config('service_registry.services.hr.connection', config('database.default'));

        $employeeUpdates = [];
        $this->mapFirstAvailableField($employeeUpdates, $profileData, 'HoTen', ['HoTen', 'TenNhanVien']);
        $this->mapFirstAvailableField($employeeUpdates, $profileData, 'GioiTinh', ['GioiTinh']);
        $this->mapFirstAvailableField($employeeUpdates, $profileData, 'NgaySinh', ['NgaySinh']);
        $this->mapFirstAvailableField($employeeUpdates, $profileData, 'Email', ['Email']);
        $this->mapFirstAvailableField($employeeUpdates, $profileData, 'DienThoai', ['DienThoai', 'SoDienThoai', 'SDT']);
        $this->mapFirstAvailableField($employeeUpdates, $profileData, 'MaBac', ['MaBac']);
        $this->mapFirstAvailableField($employeeUpdates, $profileData, 'TrangThai', ['TrangThai']);

        if ($employeeUpdates !== [] && Schema::connection($connection)->hasTable('nhanvien')) {
            foreach (array_keys($employeeUpdates) as $column) {
                if (!Schema::connection($connection)->hasColumn('nhanvien', $column)) {
                    unset($employeeUpdates[$column]);
                }
            }

            if ($employeeUpdates !== []) {
                DB::connection($connection)
                    ->table('nhanvien')
                    ->where('MaNV', $maNV)
                    ->update($employeeUpdates);
            }
        }

        $maPB = isset($profileData['MaPB']) && $profileData['MaPB'] !== '' ? (int) $profileData['MaPB'] : null;
        $maCV = isset($profileData['MaCV']) && $profileData['MaCV'] !== '' ? (int) $profileData['MaCV'] : null;

        if ($maPB !== null && $maCV !== null && Schema::connection($connection)->hasTable('phancong')) {
            $latest = DB::connection($connection)
                ->table('phancong')
                ->where('MaNV', $maNV)
                ->orderByDesc('MaQT')
                ->first();

            $isChanged = !$latest
                || (int) ($latest->MaPB ?? 0) !== $maPB
                || (int) ($latest->MaCV ?? 0) !== $maCV;

            if ($isChanged) {
                DB::connection($connection)
                    ->table('phancong')
                    ->insert([
                        'MaNV' => $maNV,
                        'MaPB' => $maPB,
                        'MaCV' => $maCV,
                        'NgayBatDau' => $profileData['NgayVaoLam'] ?? now()->toDateString(),
                        'NgayKetThuc' => null,
                        'LyDoThayDoi' => 'Cap nhat tu nhap nhanh ho so',
                    ]);
            }
        }
    }

    private function mapFirstAvailableField(array &$target, array $source, string $targetKey, array $candidateKeys): void
    {
        foreach ($candidateKeys as $key) {
            if (array_key_exists($key, $source)) {
                $target[$targetKey] = $source[$key] === '' ? null : $source[$key];
                return;
            }
        }
    }

    private function resolveModuleView(string $module, string $page): string
    {
        $meta = config("laravel_resource_modules.{$module}", []);
        $legacyFolder = (string) ($meta['legacy_name'] ?? $meta['legacy_prefix'] ?? '');

        $candidates = [
            $legacyFolder !== '' ? $legacyFolder . '.' . $page : null,
            $legacyFolder !== '' ? Str::replace('-', '_', $legacyFolder) . '.' . $page : null,
            $module . '.' . $page,
            Str::replace('-', '_', $module) . '.' . $page,
            'resource_modules.' . $page,
        ];

        foreach (array_unique(array_filter($candidates)) as $candidate) {
            if (ViewFacade::exists($candidate)) {
                return $candidate;
            }
        }

        return 'resource_modules.' . $page;
    }
}