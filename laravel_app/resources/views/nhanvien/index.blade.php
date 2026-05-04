@php $title = 'Nhân viên' @endphp
@php $subtitle = 'Danh sách và quản trị nhân sự' @endphp
@php $canCreate = in_array('them_nhanvien', session('quyen', []), true) @endphp
@php $canEdit = in_array('sua_nhanvien', session('quyen', []), true) @endphp
@php $canDelete = in_array('xoa_nhanvien', session('quyen', []), true) @endphp
@php $isSelfView = $isSelfView ?? false @endphp
@extends('layouts.app')

@section('content')
    @if ($errors->any())
        <div class="flash-alert error">
            {{ $errors->first('form') ?: $errors->first() }}
        </div>
    @endif

    @if (!$isSelfView)
    <section class="panel">
        <form method="get" action="{{ route('nhanvien.index') }}">
            <div class="field-grid">
                <div>
                    <label for="q">Tìm kiếm nhân viên</label>
                    <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nhập tên, mã, email hoặc điện thoại...">
                </div>
                <div>
                    <label for="status">Trạng thái</label>
                    <select id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="Đang làm" @selected(($filters['status'] ?? '') === 'Đang làm')>Đang làm</option>
                        <option value="Nghỉ" @selected(($filters['status'] ?? '') === 'Nghỉ')>Nghỉ</option>
                    </select>
                </div>
                <div>
                    <label for="department">Phòng ban</label>
                    <select id="department" name="department">
                        <option value="">Tất cả phòng ban</option>
                        @foreach ($options['departments'] as $department)
                            <option value="{{ $department->MaPB }}" @selected((string) ($filters['department'] ?? '') === (string) $department->MaPB)>{{ $department->TenPB }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="full-span button-row">
                    <button type="submit" class="btn">Lọc danh sách</button>
                    @if ($canCreate)
                        <a href="{{ route('nhanvien.create') }}" class="btn btn-secondary">Thêm nhân viên</a>
                    @endif
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
                        <th class="nowrap-cell">STT</th>
                        <th>Mã NV</th>
                        <th>Họ tên</th>
                        <th>Giới tính</th>
                        <th>Ngày sinh</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Bậc lương</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td><strong>{{ ($employees->firstItem() ?? 1) + $loop->index }}</strong></td>
                            <td>{{ data_get($employee, 'MaNV') }}</td>
                            <td>
                                <strong>{{ data_get($employee, 'HoTen') }}</strong>
                                <div class="muted">{{ data_get($employee, 'TenPB') ?: 'Chưa gán phòng ban' }}</div>
                            </td>
                            <td>{{ data_get($employee, 'GioiTinh') ?: 'Chưa nhập' }}</td>
                            <td>{{ data_get($employee, 'NgaySinh') ? \Illuminate\Support\Carbon::parse(data_get($employee, 'NgaySinh'))->format('d/m/Y') : 'Chưa nhập' }}</td>
                            <td>{{ data_get($employee, 'Email') ?: 'Chưa nhập' }}</td>
                            <td>{{ data_get($employee, 'DienThoai') ?: 'Chưa nhập' }}</td>
                            <td>{{ data_get($employee, 'TenBac') ?: 'Chưa có' }}</td>
                            <td>{{ data_get($employee, 'TrangThai') }}</td>
                            <td>
                                <div class="button-row">
                                    @if ($canEdit)
                                        <a href="{{ route('nhanvien.edit', ['employee' => data_get($employee, 'MaNV')]) }}" class="btn btn-secondary">Sửa</a>
                                    @endif
                                    @if ($canDelete)
                                        <form method="post" action="{{ route('nhanvien.destroy', ['employee' => data_get($employee, 'MaNV')]) }}" class="inline-form" onsubmit="return confirm('Bạn có chắc muốn xóa nhân viên này?');">
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
                            <td colspan="10" class="muted">Không có nhân viên.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employees->lastPage() > 1)
            <div class="top-gap-lg">{{ $employees->links() }}</div>
        @endif
    </section>
@endsection