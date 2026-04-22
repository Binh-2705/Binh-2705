@php $title = 'Phòng ban' @endphp
@php $subtitle = 'Danh sách và quản trị phòng ban' @endphp
@php $canCreate = in_array('them_phongban', session('quyen', []), true) @endphp
@php $canImport = in_array('import_csv_phongban', session('quyen', []), true) @endphp
@php $canExport = in_array('xuat_excel_phongban', session('quyen', []), true) @endphp
@php $canEdit = in_array('sua_phongban', session('quyen', []), true) @endphp
@php $canDelete = in_array('xoa_phongban', session('quyen', []), true) @endphp
@extends('layouts.app')

@section('content')
    <section class="panel">
        <form method="get" action="{{ route('phongban.index') }}" class="filter-grid single-wide">
            <div>
                <label for="q" class="wide-search-label">Tìm kiếm phòng ban</label>
                <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tìm tên phòng ban hoặc mô tả...">
            </div>
            <div class="button-row">
                <button type="submit" class="btn">Tìm</button>
                @if ($canCreate)
                    <a href="{{ route('phongban.create') }}" class="btn btn-secondary">Thêm phòng ban</a>
                @endif
                @if ($canImport)
                    <label class="btn btn-secondary" for="department-csv">Nhập CSV</label>
                @endif
                @if ($canExport)
                    <a href="{{ route('phongban.export-excel', request()->only(['q'])) }}" class="btn btn-secondary">Xuất Excel</a>
                @endif
            </div>
        </form>

        @if ($canImport)
            <form method="post" action="{{ route('phongban.import-csv') }}" enctype="multipart/form-data" class="field-stack top-gap-lg">
                @csrf
                <div>
                    <label for="department-csv">Tệp CSV phòng ban</label>
                    <input id="department-csv" class="file-input-reset" name="filecsv" type="file" accept=".csv,text/csv" required>
                </div>
                <div class="button-row start">
                    <button type="submit" class="btn">Tải lên</button>
                </div>
            </form>
        @endif
    </section>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead>
                    <tr>
                        <th>Mã PB</th>
                        <th>Tên phòng ban</th>
                        <th>Mô tả</th>
                        <th>Số nhân viên</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td>{{ $department->MaPB }}</td>
                            <td><strong>{{ $department->TenPB }}</strong></td>
                            <td>{{ $department->MoTa ?: 'Không có mô tả' }}</td>
                            <td>{{ $department->SoNhanVien }}</td>
                            <td>
                                <div class="button-row">
                                    @if ($canEdit)
                                        <a href="{{ route('phongban.edit', ['department' => $department->MaPB]) }}" class="btn btn-secondary">Sửa</a>
                                    @endif
                                    @if ($canDelete)
                                        <form method="post" action="{{ route('phongban.destroy', ['department' => $department->MaPB]) }}" class="inline-form" onsubmit="return confirm('Bạn có chắc muốn xóa phòng ban này?');">
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
                            <td colspan="5" class="muted">Không có phòng ban.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($departments->lastPage() > 1)
            <div class="top-gap-lg">{{ $departments->links() }}</div>
        @endif
    </section>
@endsection