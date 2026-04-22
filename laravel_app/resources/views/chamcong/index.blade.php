@php $title = 'Chấm công' @endphp
@php $subtitle = 'Quản trị bảng chấm công' @endphp
@php $canCreate = in_array('them_chamcong', session('quyen', []), true) @endphp
@php $canEdit = in_array('sua_chamcong', session('quyen', []), true) @endphp
@php $canDelete = in_array('xoa_chamcong', session('quyen', []), true) @endphp
@php $canExport = in_array('xuat_bang_cham_cong', session('quyen', []), true) @endphp
@extends('layouts.app')

@section('content')
    <section class="panel">
        <form method="get" action="{{ route('chamcong.index') }}">
            <div class="field-grid">
                <div>
                    <label for="q">Nhân viên</label>
                    <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tìm theo họ tên hoặc mã nhân viên">
                </div>
                <div>
                    <label for="status">Trạng thái</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        @foreach (['Di lam' => 'Đi làm', 'Nghi phep' => 'Nghỉ phép', 'Nghi khong luong' => 'Nghỉ không lương', 'Cong tac' => 'Công tác', 'Le' => 'Lễ'] as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" @selected(($filters['status'] ?? '') === $statusValue)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date">Ngày</label>
                    <input id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}">
                </div>
                <div class="full-span button-row">
                    <button type="submit" class="btn">Xem chấm công</button>
                    @if ($canCreate)
                        <a href="{{ route('chamcong.create') }}" class="btn btn-secondary">Thêm chấm công</a>
                    @endif
                    @if ($canExport)
                        <a href="{{ route('chamcong.export-excel', ['thang' => request('thang', now()->month), 'nam' => request('nam', now()->year)]) }}" class="btn btn-secondary">Xuất Excel</a>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead>
                    <tr>
                        <th>Mã CC</th>
                        <th>Nhân viên</th>
                        <th>Ngày</th>
                        <th>Giờ vào</th>
                        <th>Giờ ra</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->MaCC }}</td>
                            <td>
                                <strong>{{ $record->HoTen }}</strong>
                                <div class="muted">{{ $record->MaNV }}{{ $record->TenPB ? ' - ' . $record->TenPB : '' }}</div>
                            </td>
                            <td>{{ $record->Ngay ? \Illuminate\Support\Carbon::parse($record->Ngay)->format('d/m/Y') : 'Chưa nhập' }}</td>
                            <td>{{ $record->GioVao ?: '-' }}</td>
                            <td>{{ $record->GioRa ?: '-' }}</td>
                            <td>{{ $record->TrangThai }}</td>
                            <td>
                                <div class="button-row">
                                    @if ($canEdit)
                                        <a href="{{ route('chamcong.edit', ['attendance' => $record->MaCC]) }}" class="btn btn-secondary">Sửa</a>
                                    @endif
                                    @if ($canDelete)
                                        <form method="post" action="{{ route('chamcong.destroy', ['attendance' => $record->MaCC]) }}" class="inline-form" onsubmit="return confirm('Bạn có chắc muốn xóa bản ghi chấm công này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Xóa</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="muted">Không có dữ liệu chấm công.</td>
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