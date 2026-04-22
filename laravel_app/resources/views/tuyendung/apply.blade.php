@php
    $title = 'Gán ứng viên vào đợt tuyển';
    $subtitle = 'Chọn đợt tuyển đang mở cho ứng viên';
@endphp
@extends('layouts.app')

@section('content')
    <section class="panel">
        <div><strong>{{ $candidate['HoTen'] }}</strong> | CV score: {{ $candidate['DiemCV'] ?? 0 }}/10</div>
        <div class="muted top-gap-sm">{{ $candidate['Email'] ?? 'Chưa có email' }} | {{ $candidate['DienThoai'] ?? 'Chưa có số điện thoại' }}</div>
    </section>

    <section class="panel">
        <form method="post" action="{{ route('tuyendung.ungvien.attach', ['candidate' => $candidate['MaUV']]) }}">
            @csrf
            <div class="field-grid">
                <div>
                    <label for="MaDTD">Đợt tuyển</label>
                    <select id="MaDTD" name="MaDTD" required>
                        <option value="">Chọn đợt tuyển</option>
                        @foreach ($campaigns as $campaign)
                            <option value="{{ $campaign->MaDTD }}">#{{ $campaign->MaDTD }} - {{ $campaign->TenDotTuyenDung }} ({{ $campaign->ViTriTuyenDung }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="full-span">
                    <label for="GhiChu">Ghi chú</label>
                    <textarea id="GhiChu" name="GhiChu">{{ old('GhiChu') }}</textarea>
                </div>
            </div>
            <div class="form-actions-bar"><button class="btn" type="submit">Tạo hồ sơ</button><a class="btn btn-secondary" href="{{ route('tuyendung.ungvien.index') }}">Về danh sách ứng viên</a></div>
        </form>
    </section>
@endsection