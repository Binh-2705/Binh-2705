@php
    $title = 'Hồ sơ ứng tuyển';
    $subtitle = 'Theo dõi hồ sơ theo từng đợt tuyển trong hệ thống';
@endphp
@extends('layouts.app')

@section('content')
    <section class="panel">
        <div class="toolbar toolbar-start">
            <div>
                <div><strong>#{{ $campaign['MaDTD'] }} - {{ $campaign['TenDotTuyenDung'] }}</strong></div>
                <div class="muted top-gap-sm">{{ $campaign['ViTriTuyenDung'] }} | {{ $campaign['TrangThai'] }} | Từ {{ $campaign['TuNgay'] }} đến {{ $campaign['DenNgay'] ?: 'N/A' }}</div>
            </div>
            <div class="button-row spaced">
                @if (in_array('them_ho_so', session('quyen', []), true))
                    <a class="btn" href="{{ route('tuyendung.ungvien.index') }}">Chọn ứng viên</a>
                @endif
                <a class="btn btn-secondary" href="{{ route('tuyendung.index') }}">Về đợt tuyển</a>
            </div>
        </div>
    </section>

    <section class="panel">
        <form method="get" class="filter-grid">
            <div>
                <label for="q" class="wide-search-label">Tìm hồ sơ</label>
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Họ tên, email hoặc điện thoại">
            </div>
            <div>
                <label for="status" class="wide-search-label">Trạng thái</label>
                <select id="status" name="status">
                    <option value="">Tất cả</option>
                    @foreach (['Nộp hồ sơ', 'Sàng lọc', 'Phỏng vấn', 'Offer', 'Nhận việc', 'Rớt'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div><button class="btn" type="submit">Lọc</button></div>
        </form>
    </section>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead><tr>
                    <th>MãHS</th>
                    <th>Ứng viên</th>
                    <th>CV</th>
                    <th>Ngày nộp</th>
                    <th>Trạng thái</th>
                    <th>Phỏng vấn</th>
                    <th>Thao tác</th>
                </tr></thead>
                <tbody>
                @forelse ($applications as $application)
                    <tr>
                        <td>{{ $application->MaHS }}</td>
                        <td>
                            <strong>{{ $application->HoTen }}</strong>
                            <div class="muted top-gap-sm">{{ $application->Email ?: 'Chưa có email' }} | {{ $application->DienThoai ?: 'Chưa có số điện thoại' }}</div>
                        </td>
                        <td>{{ $application->DiemCV }}/10</td>
                        <td>{{ $application->NgayNop }}</td>
                        <td class="min-col-240">
                            @if (in_array('capnhat_trangthai', session('quyen', []), true))
                                <form method="post" action="{{ route('tuyendung.hoso.status', ['application' => $application->MaHS]) }}" class="review-action-form">
                                    @csrf
                                    <select name="TrangThai">
                                        @foreach (['Nộp hồ sơ', 'Sàng lọc', 'Phỏng vấn', 'Offer', 'Nhận việc', 'Rớt'] as $status)
                                            <option value="{{ $status }}" @selected($application->TrangThai === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                        <textarea name="GhiChu" placeholder="Ghi chú">{{ $application->GhiChu }}</textarea>
                                        <button class="btn" type="submit">Cập nhật</button>
                                </form>
                            @else
                                {{ $application->TrangThai }}
                            @endif
                        </td>
                        <td>{{ $application->SoLichPhongVan }}</td>
                        <td class="nowrap-cell">
                            @if (in_array('xem_lich_phong_van', session('quyen', []), true))
                                <a class="btn btn-secondary" href="{{ route('tuyendung.hoso.phongvan', ['application' => $application->MaHS]) }}">Phỏng vấn</a>
                            @endif
                            @if (!empty($application->FileCV))
                                <a class="btn" href="{{ route('legacy.upload', ['path' => 'cv/' . ltrim((string) $application->FileCV, '/')]) }}" target="_blank">CV</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Không có hồ sơ ứng tuyển trong đợt này.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="top-gap-lg">{{ $applications->links() }}</div>
    </section>
@endsection