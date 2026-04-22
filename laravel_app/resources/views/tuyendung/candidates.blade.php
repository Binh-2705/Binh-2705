@php
    $title = 'Ứng viên';
    $subtitle = 'Quản trị ứng viên và điểm CV trong hệ thống';
@endphp
@extends('layouts.app')

@section('content')
    <section class="panel">
        <form method="get" class="filter-grid">
            <div>
                <label for="q" class="wide-search-label">Tìm kiếm</label>
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Họ tên, email hoặc điện thoại">
            </div>
            <div>
                <label for="score" class="wide-search-label">Điểm CV tối thiểu</label>
                <input id="score" name="score" type="number" min="0" max="10" value="{{ $filters['score'] ?? '' }}">
            </div>
            <div class="button-row">
                <button class="btn" type="submit">Lọc</button>
                @if (in_array('them_ung_vien', session('quyen', []), true))
                    <a class="btn btn-secondary" href="{{ route('tuyendung.ungvien.create') }}">Thêm ứng viên</a>
                @endif
                <a class="btn btn-secondary" href="{{ route('tuyendung.index') }}">Đợt tuyển</a>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead><tr>
                    <th>MãUV</th>
                    <th>Họ tên</th>
                    <th>Liên hệ</th>
                    <th>Trình độ</th>
                    <th>Điểm CV</th>
                    <th>Hồ sơ</th>
                    <th>Thao tác</th>
                </tr></thead>
                <tbody>
                @forelse ($candidates as $candidate)
                    <tr>
                        <td>{{ $candidate->MaUV }}</td>
                        <td>
                            <strong>{{ $candidate->HoTen }}</strong>
                            @if (!empty($candidate->NgaySinh))
                                <div class="muted top-gap-sm">{{ $candidate->NgaySinh }} | {{ $candidate->GioiTinh ?? 'N/A' }}</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $candidate->Email ?: 'Chưa có email' }}</div>
                            <div class="muted">{{ $candidate->DienThoai ?: 'Chưa có số điện thoại' }}</div>
                        </td>
                        <td>{{ $candidate->TrinhDo ?: 'Chưa cập nhật' }}</td>
                        <td><strong>{{ $candidate->DiemCV }}</strong>/10</td>
                        <td>{{ $candidate->SoHoSo }}</td>
                        <td class="nowrap-cell">
                            @if (!empty($candidate->FileCV))
                                <a class="btn btn-secondary" href="{{ route('legacy.upload', ['path' => 'cv/' . ltrim((string) $candidate->FileCV, '/')]) }}" target="_blank">CV</a>
                            @endif
                            @if (in_array('them_ho_so', session('quyen', []), true))
                                <a class="btn" href="{{ route('tuyendung.ungvien.apply', ['candidate' => $candidate->MaUV]) }}">Chọn đợt</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Không có ứng viên phù hợp.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="top-gap-lg">{{ $candidates->links() }}</div>
    </section>
@endsection