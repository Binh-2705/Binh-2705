@php
    $title = 'Đào tạo';
    $subtitle = 'Quản trị khóa đào tạo trên hệ thống';
@endphp
@extends('layouts.app')

@section('content')
<section class="panel">
    <form method="get" class="filter-grid">
        <div><label for="q" class="wide-search-label">Tìm kiếm</label><input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên khóa hoặc đơn vị tổ chức"></div>
        <div><label for="status" class="wide-search-label">Trạng thái</label><select id="status" name="status"><option value="">Tất cả</option>@foreach (['Lên kế hoạch','Đang đào tạo','Hoàn thành'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>@endforeach</select></div>
        <div class="button-row"><button class="btn" type="submit">Lọc</button>@if (in_array('them_khoa_dao_tao', session('quyen', []), true))<a class="btn btn-secondary" href="{{ route('daotao.create') }}">Thêm mới</a>@endif</div>
    </form>
</section>

<section class="panel">
    <div class="table-shell"><table class="data-table table-compact"><thead><tr><th>MãKDT</th><th>Tên khóa</th><th>Đơn vị</th><th>Học viên</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>@forelse ($courses as $course)<tr><td>{{ $course->MaKDT }}</td><td><strong>{{ $course->TenKhoaDaoTao }}</strong></td><td>{{ $course->DonViToChuc ?: 'Nội bộ' }}</td><td>{{ $course->SoHocVien }}</td><td>{{ $course->TrangThai }}</td><td><div class="button-row">@if (in_array('xem_tham_gia_dao_tao', session('quyen', []), true))<a class="btn btn-secondary" href="{{ route('daotao.hocvien', ['training' => $course->MaKDT]) }}">Học viên</a>@endif @if (in_array('them_khoa_dao_tao', session('quyen', []), true))<a class="btn btn-secondary" href="{{ route('daotao.edit', ['training' => $course->MaKDT]) }}">Sửa</a>@endif @if (in_array('xoa_khoa_dao_tao', session('quyen', []), true))<form method="post" action="{{ route('daotao.destroy', ['training' => $course->MaKDT]) }}" class="inline-form" onsubmit="return confirm('Xóa khóa đào tạo này?');">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Xóa</button></form>@endif</div></td></tr>@empty<tr><td colspan="6" class="muted">Không có dữ liệu đào tạo.</td></tr>@endforelse</tbody></table></div>
    <div class="top-gap-lg">{{ $courses->links() }}</div>
</section>
@endsection