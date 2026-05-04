@php $title = 'Lương' @endphp
@php $subtitle = 'Quản trị bảng lương' @endphp
@php $canRun = in_array('tinh_luong_thang', session('quyen', []), true) @endphp
@php $canView = in_array('xem_luong', session('quyen', []), true) @endphp
@php $canLock = in_array('chot_luong', session('quyen', []), true) @endphp
@php $canUnlock = in_array('mo_chot_luong', session('quyen', []), true) @endphp
@php $canEdit = in_array('mo_chot_luong', session('quyen', []), true) || in_array('chot_luong', session('quyen', []), true) @endphp
@php $isSelfView = $isSelfView ?? false @endphp
@extends('layouts.app')

@section('content')
    @if ($canRun && !$isSelfView)
        <section class="panel">
            <form method="post" action="{{ route('luong.run-monthly') }}">
                @csrf
                <div class="field-grid">
                    <div>
                        <label for="run-month">Tháng tính lương</label>
                        <input id="run-month" type="number" name="thang" min="1" max="12" value="{{ request('month', now()->month) }}" required>
                    </div>
                    <div>
                        <label for="run-year">Năm</label>
                        <input id="run-year" type="number" name="nam" value="{{ request('year', now()->year) }}" required>
                    </div>
                    <div class="full-span button-row">
                        <button type="submit" class="btn">Tính lương tháng</button>
                        <a href="{{ route('luong.create') }}" class="btn btn-secondary">Thêm bảng lương</a>
                    </div>
                </div>
            </form>
        </section>
    @endif

    @if (!$isSelfView)
    <section class="panel">
        <form method="get" action="{{ route('luong.index') }}">
            <div class="field-grid">
                <div>
                    <label for="q">Nhân viên</label>
                    <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tìm theo họ tên hoặc mã nhân viên">
                </div>
                <div>
                    <label for="month">Tháng</label>
                    <input id="month" name="month" type="number" min="1" max="12" value="{{ $filters['month'] ?? '' }}" placeholder="Tháng">
                </div>
                <div>
                    <label for="year">Năm</label>
                    <input id="year" name="year" type="number" min="2000" max="2100" value="{{ $filters['year'] ?? '' }}" placeholder="Năm">
                </div>
                <div>
                    <label for="status">Trạng thái</label>
                    <input id="status" name="status" value="{{ $filters['status'] ?? '' }}" placeholder="Chưa chốt / Đã chốt">
                </div>
                <div class="full-span button-row">
                    <button class="btn" type="submit">Lọc bảng lương</button>
                </div>
            </div>
        </form>
    </section>
    @endif

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead>
                    <tr>
                        <th>Mã BL</th>
                        <th>Nhân viên</th>
                        <th>Tháng</th>
                        <th>Năm</th>
                        <th>Thực nhận</th>
                        <th>Tổng lương</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->MaBL }}</td>
                            <td>
                                <strong>{{ $record->HoTen }}</strong>
                                <div class="muted">Mã NV: {{ $record->MaNV }}</div>
                            </td>
                            <td>{{ $record->Thang }}</td>
                            <td>{{ $record->Nam }}</td>
                            <td><strong class="metric-value-danger">{{ number_format((float) $record->TongLuong, 0, ',', '.') }}</strong></td>
                            <td><strong class="metric-value-danger">{{ number_format((float) $record->TongLuong, 0, ',', '.') }}</strong></td>
                            <td>
                                @if ($record->TrangThai === 'Đã chốt')
                                    <span class="status-text-ok">Đã chốt</span>
                                @else
                                    <span class="status-text-warn">{{ $record->TrangThai }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($canView || $canEdit || $canLock || $canUnlock)
                                    <div class="button-row">
                                        @if ($canView)
                                            <a href="{{ route('luong.show', ['payroll' => $record->MaBL]) }}" class="btn btn-secondary">Xem</a>
                                        @endif
                                        @if ($canEdit)
                                            <a href="{{ route('luong.edit', ['payroll' => $record->MaBL]) }}" class="btn btn-secondary">Sửa</a>
                                        @endif
                                        @if ($record->TrangThai !== 'Đã chốt' && $canLock)
                                            <a href="{{ route('luong.lock.legacy', ['payroll' => $record->MaBL]) }}" class="btn">Chốt lương</a>
                                        @endif
                                        @if ($record->TrangThai === 'Đã chốt' && $canUnlock)
                                            <a href="{{ route('luong.unlock.legacy', ['payroll' => $record->MaBL]) }}" class="btn btn-secondary">Mở chốt</a>
                                        @endif
                                    </div>
                                @else
                                    <span class="muted-inline-note">Chỉ xem</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="muted">Chưa có dữ liệu lương.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->lastPage() > 1)
            <div class="top-gap-lg">{{ $records->links() }}</div>
        @endif
    </section>
@endsection