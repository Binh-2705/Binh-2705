@php
    $title = 'Tuyển dụng';
    $subtitle = 'Quản trị đợt tuyển dụng trên hệ thống';
@endphp
@extends('layouts.app')

@section('content')
    <section class="panel">
        <form method="get" class="filter-grid">
            <div>
                <label for="q" class="wide-search-label">Tìm kiếm</label>
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên đợt hoặc vị trí">
            </div>
            <div>
                <label for="status" class="wide-search-label">Trạng thái</label>
                <select id="status" name="status">
                    <option value="">Tất cả</option>
                    <option value="Đang tuyển" @selected(($filters['status'] ?? '') === 'Đang tuyển')>Đang tuyển</option>
                    <option value="Đã kết thúc" @selected(($filters['status'] ?? '') === 'Đã kết thúc')>Đã kết thúc</option>
                </select>
            </div>
            <div class="button-row">
                <button class="btn" type="submit">Lọc</button>
                @if (in_array('them_dot_tuyen', session('quyen', []), true))
                    <a class="btn btn-secondary" href="{{ route('tuyendung.create') }}">Thêm mới</a>
                @endif
                @if (in_array('xem_ung_vien', session('quyen', []), true))
                    <a class="btn btn-secondary" href="{{ route('tuyendung.ungvien.index') }}">Ứng viên</a>
                @endif
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead><tr>
                    <th>MãDTD</th>
                    <th>Tên đợt</th>
                    <th>Vị trí</th>
                    <th>Số lượng</th>
                    <th>Hồ sơ</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr></thead>
                <tbody>
                @forelse ($campaigns as $campaign)
                    <tr>
                        <td>{{ $campaign->MaDTD }}</td>
                        <td><strong>{{ $campaign->TenDotTuyenDung }}</strong></td>
                        <td>{{ $campaign->ViTriTuyenDung }}</td>
                        <td>{{ $campaign->SoLuong }}</td>
                        <td>{{ $campaign->SoHoSo }}</td>
                        <td>{{ $campaign->TrangThai }}</td>
                        <td>
                            <div class="button-row">
                            @if (in_array('xem_ho_so', session('quyen', []), true))
                                <a class="btn" href="{{ route('tuyendung.hoso.index', ['recruitment' => $campaign->MaDTD]) }}">Hồ sơ</a>
                            @endif
                            @if (in_array('them_dot_tuyen', session('quyen', []), true))
                                <a class="btn btn-secondary" href="{{ route('tuyendung.edit', ['recruitment' => $campaign->MaDTD]) }}">Sửa</a>
                            @endif
                            @if (in_array('xoa_dot_tuyen', session('quyen', []), true))
                                <form method="post" action="{{ route('tuyendung.destroy', ['recruitment' => $campaign->MaDTD]) }}" class="inline-form" onsubmit="return confirm('Xóa đợt tuyển dụng này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" type="submit">Xóa</button>
                                </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Không có dữ liệu tuyển dụng.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="top-gap-lg">{{ $campaigns->links() }}</div>
    </section>
@endsection