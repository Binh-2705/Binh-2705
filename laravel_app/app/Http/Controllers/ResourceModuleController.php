<?php

namespace App\Http\Controllers;

use App\Services\GenericResourceModuleService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;
use LogicException;

class ResourceModuleController extends Controller
{
    public function __construct(private GenericResourceModuleService $modules)
    {
    }

    public function index(Request $request, string $module): View
    {
        $meta = $this->modules->describe($module);
        $items = $this->modules->paginate($module, $request->only(['q']));
        $items->appends($request->query());

        return view($this->resolveModuleView($module, 'index'), [
            'moduleKey' => $module,
            'routeKey' => $meta['module']['legacy_name'] ?? $module,
            'moduleConfig' => $meta['module'],
            'resourceConfig' => $meta['resource'],
            'items' => $items,
            'filters' => $request->only(['q']),
        ]);
    }

    public function create(string $module): View
    {
        $meta = $this->modules->describe($module);
        abort_if($meta['resource']['read_only'] ?? false, 404);

        return view($this->resolveModuleView($module, 'form'), [
            'mode' => 'create',
            'moduleKey' => $module,
            'routeKey' => $meta['module']['legacy_name'] ?? $module,
            'moduleConfig' => $meta['module'],
            'resourceConfig' => $meta['resource'],
            'record' => [],
            'recordId' => null,
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        $meta = $this->modules->describe($module);
        $routeKey = $meta['module']['legacy_name'] ?? $module;

        try {
            $recordId = $this->modules->create($module, $this->buildPayload($request, $meta['resource'], false));

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
        $routeKey = $meta['module']['legacy_name'] ?? $module;

        try {
            $this->modules->update($module, $record, $this->buildPayload($request, $meta['resource'], true));

            return redirect()->route("{$routeKey}.edit", ['record' => $record])
                ->with('success', 'Da cap nhat ban ghi thanh cong.');
        } catch (QueryException|LogicException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the cap nhat ban ghi: ' . $exception->getMessage()]);
        }
    }

    public function destroy(string $module, string $record): RedirectResponse
    {
        $meta = $this->modules->describe($module);
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

        DB::connection($this->moduleConnection($module))
            ->table('baohiem')
            ->where('MaBH', $record)
            ->update(['TrangThai' => 'Ngừng']);

        return redirect()->route('baohiem.index')->with('success', 'Da ngung bao hiem thanh cong.');
    }

    public function approveLeaveRequest(string $module, string $record): RedirectResponse
    {
        abort_unless($module === 'leave-requests', 404);

        $connection = DB::connection($this->moduleConnection($module));
        $leaveId = (int) $record;

        try {
            $result = $connection->transaction(function () use ($connection, $leaveId) {
                $leave = $connection->table('nghiphep')->where('MaNP', $leaveId)->lockForUpdate()->first();
                if ($leave === null) {
                    return false;
                }

                $connection->table('nghiphep')->where('MaNP', $leaveId)->update([
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

                return true;
            });

            return redirect()->route('nghiphep.index')->with(
                $result ? 'success' : 'error',
                $result ? 'Da duyet don nghi phep thanh cong.' : 'Khong the duyet don nghi phep.'
            );
        } catch (\Throwable) {
            return redirect()->route('nghiphep.index')->with('error', 'Khong the duyet don nghi phep.');
        }
    }

    public function rejectLeaveRequest(string $module, string $record): RedirectResponse
    {
        abort_unless($module === 'leave-requests', 404);

        DB::connection($this->moduleConnection($module))
            ->table('nghiphep')
            ->where('MaNP', $record)
            ->update([
                'TrangThai' => 'Từ chối',
                'NgayDuyet' => now()->toDateString(),
            ]);

        return redirect()->route('nghiphep.index')->with('success', 'Da tu choi don nghi phep thanh cong.');
    }

    public function exportExcel(Request $request, string $module): StreamedResponse
    {
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

    private function buildPayload(Request $request, array $resourceConfig, bool $isUpdate): array
    {
        $payload = [];
        $primaryKeys = is_array($resourceConfig['primary_key'] ?? null)
            ? $resourceConfig['primary_key']
            : [(string) ($resourceConfig['primary_key'] ?? 'id')];

        foreach ($resourceConfig['columns'] as $column) {
            $field = (string) ($column['field'] ?? '');
            $isAutoIncrement = str_contains((string) ($column['extra'] ?? ''), 'auto_increment');

            if ($field === '' || ($isUpdate && in_array($field, $primaryKeys, true)) || $isAutoIncrement) {
                continue;
            }

            if (!$request->has($field)) {
                continue;
            }

            $value = $request->input($field);
            $payload[$field] = $value === '' ? null : $value;
        }

        return $payload;
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

    private function moduleConnection(string $module): string
    {
        $meta = $this->modules->describe($module);
        $service = (string) ($meta['module']['service'] ?? 'hr');

        return (string) config("service_registry.services.{$service}.connection", config('database.default'));
    }
}