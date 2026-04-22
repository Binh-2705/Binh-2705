@php $title = $mode === 'create' ? 'Thêm bảng lương' : 'Sửa bảng lương' @endphp
@php $subtitle = 'Tính toán và điều chỉnh bảng lương' @endphp
@extends('layouts.app')

@section('content')
    <section class="panel">
        <form method="post" action="{{ $mode === 'create' ? route('luong.store') : route('luong.update', ['payroll' => $record['MaBL']]) }}">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="field-grid">
                <div>
                    <label for="MaNV">Chọn nhân viên</label>
                    <select id="MaNV" name="MaNV" required>
                        <option value="">-- Chọn nhân viên --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->MaNV }}" @selected((string) old('MaNV', $record['MaNV'] ?? '') === (string) $employee->MaNV)>{{ $employee->HoTen }} ({{ $employee->MaNV }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="Thang">Tháng</label>
                    <input id="Thang" type="number" name="Thang" min="1" max="12" value="{{ old('Thang', $record['Thang'] ?? now()->month) }}" required>
                </div>
                <div>
                    <label for="Nam">Năm</label>
                    <input id="Nam" type="number" name="Nam" min="2000" max="2100" value="{{ old('Nam', $record['Nam'] ?? now()->year) }}" required>
                </div>
                <div>
                    <label for="LuongCoSo">Lương cơ bản</label>
                    <input id="LuongCoSo" type="number" step="0.01" name="LuongCoSo" value="{{ old('LuongCoSo', $record['LuongCoSo'] ?? '') }}">
                </div>
                <div>
                    <label for="HeSoLuong">Hệ số lương</label>
                    <input id="HeSoLuong" type="number" step="0.01" name="HeSoLuong" value="{{ old('HeSoLuong', $record['HeSoLuong'] ?? '') }}">
                </div>
                <div>
                    <label for="HeSoChucVu">Hệ số chức vụ</label>
                    <input id="HeSoChucVu" type="number" step="0.01" name="HeSoChucVu" value="{{ old('HeSoChucVu', $record['HeSoChucVu'] ?? '') }}">
                </div>
                <div>
                    <label for="PhuCap">Phụ cấp (VNĐ)</label>
                    <input id="PhuCap" type="number" step="0.01" name="PhuCap" value="{{ old('PhuCap', $record['PhuCap'] ?? '') }}">
                </div>
                <div>
                    <label for="Thuong">Thưởng (VNĐ)</label>
                    <input id="Thuong" type="number" step="0.01" name="Thuong" value="{{ old('Thuong', $record['Thuong'] ?? '') }}">
                </div>
                <div>
                    <label for="Phat">Phạt (VNĐ)</label>
                    <input id="Phat" type="number" step="0.01" name="Phat" value="{{ old('Phat', $record['Phat'] ?? '') }}">
                </div>
                <div>
                    <label for="BaoHiem">Bảo hiểm (VNĐ)</label>
                    <input id="BaoHiem" type="number" step="0.01" name="BaoHiem" value="{{ old('BaoHiem', $record['BaoHiem'] ?? '') }}">
                </div>
                <div>
                    <label for="TongLuong">Tổng lương (VNĐ)</label>
                    <input id="TongLuong" type="number" step="0.01" name="TongLuong" value="{{ old('TongLuong', $record['TongLuong'] ?? '') }}">
                </div>
                <div>
                    <label for="TrangThai">Trạng thái</label>
                    <input id="TrangThai" type="text" name="TrangThai" value="{{ old('TrangThai', $record['TrangThai'] ?? 'Chưa chốt') }}" required>
                </div>
                <div>
                    <label for="NgayTinh">Ngày tính</label>
                    <input id="NgayTinh" type="date" name="NgayTinh" value="{{ old('NgayTinh', $record['NgayTinh'] ?? now()->toDateString()) }}">
                </div>
            </div>

            <div class="form-actions-bar">
                <button type="submit" class="btn">Lưu bảng lương</button>
                <a href="{{ route('luong.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>
    </section>
@endsection