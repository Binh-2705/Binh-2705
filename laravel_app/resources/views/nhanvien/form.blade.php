@php $title = $mode === 'create' ? 'Thêm nhân viên' : 'Sửa nhân viên' @endphp
@php $subtitle = 'Quản trị thông tin nhân sự và phân công hiện tại' @endphp
@extends('layouts.app')

@section('content')
    @if ($errors->any())
        <div class="flash-alert error">
            {{ $errors->first('form') ?: $errors->first() }}
        </div>
    @endif

    <section class="panel">
        <form method="post" action="{{ $mode === 'create' ? route('nhanvien.store') : route('nhanvien.update', ['employee' => $employee['MaNV']]) }}">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="field-grid">
                <div>
                    <label for="HoTen">Họ và tên</label>
                    <input id="HoTen" type="text" name="HoTen" value="{{ old('HoTen', $employee['HoTen'] ?? '') }}" required maxlength="120" placeholder="VD: Nguyễn Văn A">
                </div>
                <div>
                    <label for="GioiTinh">Giới tính</label>
                    <select id="GioiTinh" name="GioiTinh">
                        <option value="">-- Chọn giới tính --</option>
                        <option value="Nam" @selected(old('GioiTinh', $employee['GioiTinh'] ?? '') === 'Nam')>Nam</option>
                        <option value="Nữ" @selected(old('GioiTinh', $employee['GioiTinh'] ?? '') === 'Nữ')>Nữ</option>
                    </select>
                </div>
                <div>
                    <label for="NgaySinh">Ngày sinh</label>
                    <input id="NgaySinh" type="date" name="NgaySinh" value="{{ old('NgaySinh', $employee['NgaySinh'] ?? '') }}">
                </div>
                <div>
                    <label for="Email">Email</label>
                    <input id="Email" type="email" name="Email" value="{{ old('Email', $employee['Email'] ?? '') }}" maxlength="150" placeholder="VD: nguyenvana@example.com">
                </div>
                <div>
                    <label for="DienThoai">Điện thoại</label>
                    <input id="DienThoai" type="tel" name="DienThoai" value="{{ old('DienThoai', $employee['DienThoai'] ?? '') }}" maxlength="20" placeholder="VD: 0901234567">
                </div>
                <div>
                    <label for="TrangThai">Trạng thái</label>
                    <select id="TrangThai" name="TrangThai" required>
                        <option value="Đang làm" @selected(old('TrangThai', $employee['TrangThai'] ?? 'Đang làm') === 'Đang làm')>Đang làm</option>
                        <option value="Nghỉ" @selected(old('TrangThai', $employee['TrangThai'] ?? '') === 'Nghỉ')>Nghỉ</option>
                    </select>
                </div>
                <div>
                    <label for="MaPB">Phòng ban</label>
                    <select id="MaPB" name="MaPB">
                        <option value="">-- Chọn phòng ban --</option>
                        @foreach ($options['departments'] as $department)
                            <option value="{{ $department->MaPB }}" @selected((string) old('MaPB', $employee['CurrentMaPB'] ?? '') === (string) $department->MaPB)>{{ $department->TenPB }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="MaCV">Chức vụ</label>
                    <select id="MaCV" name="MaCV">
                        <option value="">-- Chọn chức vụ --</option>
                        @foreach ($options['positions'] as $position)
                            <option value="{{ $position->MaCV }}" @selected((string) old('MaCV', $employee['CurrentMaCV'] ?? '') === (string) $position->MaCV)>{{ $position->TenCV }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="MaBac">Bậc lương</label>
                    <select id="MaBac" name="MaBac">
                        <option value="">-- Chọn bậc lương --</option>
                        @foreach ($options['salaryGrades'] as $grade)
                            <option value="{{ $grade->MaBac }}" @selected((string) old('MaBac', $employee['MaBac'] ?? '') === (string) $grade->MaBac)>{{ $grade->TenBac }} (HS: {{ $grade->HeSoLuong }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="NgayVaoLam">Ngày vào làm</label>
                    <input id="NgayVaoLam" type="date" name="NgayVaoLam" value="{{ old('NgayVaoLam', $employee['NgayVaoLam'] ?? '') }}">
                </div>
                <div class="full-span">
                    <label for="DiaChi">Địa chỉ</label>
                    <textarea id="DiaChi" name="DiaChi" placeholder="Nhập địa chỉ hiện tại...">{{ old('DiaChi', $employee['DiaChi'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="form-actions-bar">
                <button type="submit" class="btn">Lưu nhân viên</button>
                <a href="{{ route('nhanvien.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </section>
@endsection