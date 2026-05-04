@php
    $hiddenColumns = array_map('strtolower', $moduleConfig['hidden_columns'] ?? []);
    $columns = collect($resourceConfig['columns'])->pluck('field')->filter(fn ($field) => $field !== '__resource_id' && !in_array(strtolower($field), $hiddenColumns, true))->take(8)->values();
    $disableFilter = (bool) ($moduleConfig['disable_filter'] ?? false);
    $disableExport = (bool) ($moduleConfig['disable_export'] ?? false);
    $hideActions = (bool) ($moduleConfig['hide_actions'] ?? false);
    $isSelfView = (bool) ($isSelfView ?? false);
@endphp
<section class="panel">
    @if (!$disableFilter && !$isSelfView)
    <form method="get" class="filter-grid single-wide">
        <div>
            <label for="q" class="wide-search-label">Tìm kiếm</label>
            <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tìm trong danh mục này">
        </div>
        <div class="button-row">
            <button class="btn" type="submit">Lọc</button>
            @if (!$disableExport && in_array($moduleConfig['permission']['view'], session('quyen', []), true))
                <a class="btn btn-secondary" href="{{ route(($routeKey ?? $moduleKey) . '.export-excel', request()->only(['q'])) }}">Xuất Excel</a>
            @endif
            @if ($moduleKey === 'employee-profiles')
                @php
                    $viewerRole = strtolower(trim((string) data_get(session('taikhoan', []), 'VaiTro', '')));
                @endphp
                @if (in_array($viewerRole, ['admin', 'quanly'], true))
                    <a class="btn btn-secondary" href="{{ route('hosocanhan.review-requests') }}">Duyệt yêu cầu</a>
                @endif
            @endif
            @if (!($resourceConfig['read_only'] ?? false) && in_array($moduleConfig['permission']['create'], session('quyen', []), true))
                <a class="btn btn-secondary" href="{{ route(($routeKey ?? $moduleKey) . '.create') }}">Thêm mới</a>
            @endif
        </div>
    </form>
    @endif
</section>

<section class="panel">
    <div class="table-shell">
        <table class="data-table table-compact">
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                    @if (!$hideActions)
                    <th>Thao tác</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        @foreach ($columns as $column)
                            <td>{{ data_get($item, $column) }}</td>
                        @endforeach
                        @if (!$hideActions)
                        <td>
                            @if ($resourceConfig['read_only'] ?? false)
                                <span class="muted">Chỉ xem</span>
                            @else
                                <div class="button-row">
                                @if ($moduleKey === 'employee-profiles' && in_array($moduleConfig['permission']['view'], session('quyen', []), true))
                                    <a class="btn btn-secondary" href="{{ route('hosocanhan.show', ['profile' => data_get($item, '__resource_id')]) }}">Xem</a>
                                @endif
                                @if (in_array($moduleConfig['permission']['update'], session('quyen', []), true))
                                    <a class="btn btn-secondary" href="{{ route(($routeKey ?? $moduleKey) . '.edit', ['record' => data_get($item, '__resource_id')]) }}">Sửa</a>
                                    @if ($moduleKey === 'accounts')
                                        <form method="post" action="{{ route('taikhoan.reset-temporary', ['account' => data_get($item, '__resource_id')]) }}" class="inline-form" onsubmit="return confirm('Cấp mật khẩu tạm cho tài khoản này?');">
                                            @csrf
                                            <button class="btn btn-secondary" type="submit">Cấp lại mật khẩu tạm</button>
                                        </form>
                                    @endif
                                @endif
                                @if ($moduleKey === 'contracts')
                                    @if (in_array('giahan_hopdong', session('quyen', []), true))
                                        <a class="btn btn-secondary" href="{{ route('hopdong.renew', ['contract' => data_get($item, '__resource_id')]) }}">Gia hạn</a>
                                    @endif
                                    @if (in_array('chamdut_hopdong', session('quyen', []), true))
                                        <form method="post" action="{{ route('hopdong.terminate', ['contract' => data_get($item, '__resource_id')]) }}" class="inline-form" onsubmit="return confirm('Chấm dứt hợp đồng này?');">
                                            @csrf
                                            <button class="btn btn-danger" type="submit">Chấm dứt</button>
                                        </form>
                                    @endif
                                    @if (in_array('xem_lich_su_luong', session('quyen', []), true))
                                        <a class="btn btn-secondary" href="{{ route('hopdong.salary-history', ['contract' => data_get($item, '__resource_id')]) }}">Lịch sử lương</a>
                                    @endif
                                @endif
                                @if (in_array($moduleConfig['permission']['delete'], session('quyen', []), true))
                                    <form method="post" action="{{ route(($routeKey ?? $moduleKey) . '.destroy', ['record' => data_get($item, '__resource_id')]) }}" class="inline-form" onsubmit="return confirm('Xóa bản ghi này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">Xóa</button>
                                    </form>
                                @endif
                                </div>
                            @endif
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $columns->count() + ($hideActions ? 0 : 1) }}" class="muted">Không có dữ liệu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="top-gap-lg">{{ $items->links() }}</div>
</section>
