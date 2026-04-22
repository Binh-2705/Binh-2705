@php
    $title = 'Phỏng vấn và đánh giá';
    $subtitle = 'Quản lý lịch phỏng vấn và nhận xét ứng viên trong hệ thống';
@endphp
@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="toolbar toolbar-start">
            <div>
                <div><strong>{{ $application['HoTen'] }}</strong> - {{ $application['TenDotTuyenDung'] }}</div>
                <div class="muted top-gap-sm">MãHS: {{ $application['MaHS'] }} | Trạng thái: {{ $application['TrangThai'] }} | Điểm CV: {{ $application['DiemCV'] }}/10</div>
            </div>
            <div class="button-row spaced">
                <a class="btn btn-secondary" href="{{ route('tuyendung.hoso.index', ['recruitment' => $application['MaDTD']]) }}">Về hồ sơ</a>
                @if (!empty($application['FileCV']))
                    <a class="btn" href="{{ route('legacy.upload', ['path' => 'cv/' . ltrim((string) $application['FileCV'], '/')]) }}" target="_blank">Mở CV</a>
                @endif
            </div>
        </div>
    </section>

    <section class="split-two">
        <div class="panel">
            <h3 class="no-top-margin">Thêm lịch phỏng vấn</h3>
            <form method="post" action="{{ route('tuyendung.hoso.phongvan.store', ['application' => $application['MaHS']]) }}">
                @csrf
                <div class="field-stack">
                    <div><label for="NgayPhongVan">Ngày phỏng vấn</label><input id="NgayPhongVan" name="NgayPhongVan" type="date" required></div>
                    <div><label for="GioPhongVan">Giờ phỏng vấn</label><input id="GioPhongVan" name="GioPhongVan" type="time" required></div>
                    <div><label for="DiaDiem">Địa điểm</label><input id="DiaDiem" name="DiaDiem"></div>
                    <div><label for="GhiChu">Ghi chú</label><textarea id="GhiChu" name="GhiChu"></textarea></div>
                    <div><label for="KetQua">Kết quả</label><input id="KetQua" name="KetQua"></div>
                    <button class="btn" type="submit">Thêm lịch</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <h3 class="no-top-margin">Thêm đánh giá</h3>
            <form method="post" action="{{ route('tuyendung.hoso.danhgia.store', ['application' => $application['MaHS']]) }}">
                @csrf
                <div class="field-stack">
                    <div><label for="DiemKyNang">Kỹ năng</label><input id="DiemKyNang" name="DiemKyNang" type="number" min="1" max="10" required></div>
                    <div><label for="DiemKinhNghiem">Kinh nghiệm</label><input id="DiemKinhNghiem" name="DiemKinhNghiem" type="number" min="1" max="10" required></div>
                    <div><label for="DiemThaiDo">Thái độ</label><input id="DiemThaiDo" name="DiemThaiDo" type="number" min="1" max="10" required></div>
                    <div><label for="NhanXet">Nhận xét</label><textarea id="NhanXet" name="NhanXet"></textarea></div>
                    <button class="btn" type="submit">Lưu đánh giá</button>
                </div>
            </form>
        </div>
    </section>

    <section class="split-two">
        <div class="panel">
            <h3 class="no-top-margin">Danh sách lịch phỏng vấn</h3>
            <div class="stack-list">
                @forelse ($interviews as $interview)
                    <div class="stack-card-soft">
                        <div><strong>{{ $interview->NgayPhongVan }}</strong> lúc {{ $interview->GioPhongVan }}</div>
                        <div class="muted top-gap-sm">{{ $interview->DiaDiem ?: 'Chưa có địa điểm' }}</div>
                        @if (!empty($interview->GhiChu))
                            <div class="muted top-gap-sm">{{ $interview->GhiChu }}</div>
                        @endif
                        @if (!empty($interview->KetQua))
                            <div class="top-gap-sm"><strong>Kết quả:</strong> {{ $interview->KetQua }}</div>
                        @endif
                    </div>
                @empty
                    <div class="muted">Chưa có lịch phỏng vấn.</div>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <h3 class="no-top-margin">Đánh giá đã lưu</h3>
            <div class="stack-list">
                @forelse ($reviews as $review)
                    @php
                        $average = ($review->DiemKyNang + $review->DiemKinhNghiem + $review->DiemThaiDo) / 3;
                    @endphp
                    <div class="stack-card-soft">
                        <div><strong>Điểm TB:</strong> {{ number_format($average, 1) }}/10</div>
                        <div class="muted top-gap-sm">Kỹ năng: {{ $review->DiemKyNang }} | Kinh nghiệm: {{ $review->DiemKinhNghiem }} | Thái độ: {{ $review->DiemThaiDo }}</div>
                        @if (!empty($review->NhanXet))
                            <div class="top-gap-sm">{{ $review->NhanXet }}</div>
                        @endif
                    </div>
                @empty
                    <div class="muted">Chưa có đánh giá phỏng vấn.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection