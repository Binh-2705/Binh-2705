@php $title = 'Gia hạn hợp đồng' @endphp
@php $subtitle = 'Gia hạn hợp đồng lao động trên hệ thống' @endphp
@extends('layouts.app')

@section('content')
<section class="panel">
    <div class="muted">Hợp đồng gốc</div>
    <div class="metric-strong top-gap-sm">{{ $contract['SoHopDong'] }} · {{ $contract['HoTen'] }}</div>
    <div class="muted top-gap-sm">Bậc hiện tại: {{ $contract['TenBac'] }} · Lương hiện tại: {{ number_format($contract['LuongThucTe'], 0, ',', '.') }} VNĐ</div>
</section>

<section class="panel">
    <form method="post" action="{{ route('hopdong.renew.store', ['contract' => $contract['MaHopDong']]) }}">
        @csrf
        <div class="field-grid">
            <div>
                <label for="SoHopDong">Số hợp đồng mới</label>
                <input id="SoHopDong" name="SoHopDong" value="{{ old('SoHopDong') }}" required>
            </div>
            <div>
                <label for="LoaiHopDong">Loại hợp đồng</label>
                <select id="LoaiHopDong" name="LoaiHopDong" required>
                    @foreach (['Thử việc', 'Xác định thời hạn', 'Không xác định thời hạn'] as $type)
                        <option value="{{ $type }}" @selected(old('LoaiHopDong', $contract['LoaiHopDong']) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="NgayBatDau">Ngày bắt đầu mới</label>
                <input id="NgayBatDau" name="NgayBatDau" type="date" value="{{ old('NgayBatDau', now()->toDateString()) }}" required>
            </div>
            <div>
                <label for="NgayKetThuc">Ngày kết thúc mới</label>
                <input id="NgayKetThuc" name="NgayKetThuc" type="date" value="{{ old('NgayKetThuc') }}">
            </div>
            <div class="full-span">
                <label for="GhiChu">Ghi chú</label>
                <textarea id="GhiChu" name="GhiChu">{{ old('GhiChu', 'Gia hạn từ hợp đồng số ' . $contract['SoHopDong']) }}</textarea>
            </div>
        </div>
        <div class="form-actions-bar">
            <button class="btn" type="submit">Xác nhận gia hạn</button>
            <a class="btn btn-secondary" href="{{ route('hopdong.index') }}">Hủy bỏ</a>
        </div>
    </form>
</section>
@endsection