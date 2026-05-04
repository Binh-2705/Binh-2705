@php
    $title = 'Danh sách ứng viên';
    $subtitle = 'Quản lý ứng viên, đánh giá CV và nộp hồ sơ theo đợt tuyển';
@endphp
@extends('layouts.app')

@section('content')
    @php
        $permissions = (array) session('quyen', []);
    @endphp
    <section class="panel">
        <div class="button-row">
            @if (in_array('them_ung_vien', $permissions, true))
                <a class="btn" href="{{ route('tuyendung.ungvien.create') }}">+ Thêm ứng viên</a>
            @endif
            <a class="btn btn-secondary" href="{{ route('tuyendung.index') }}">Đợt tuyển</a>
        </div>
        <form method="get" class="top-gap-md">
            <div class="toolbar toolbar-start">
                <div>
                    <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nhập tên, email hoặc số điện thoại" style="max-width:360px;">
                </div>
                <div class="button-row">
                    <button class="btn" type="submit">Tìm</button>
                </div>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-shell">
            <table class="data-table table-compact">
                <thead><tr>
                    <th>Mã</th>
                    <th>Họ tên</th>
                    <th>Ngày sinh</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Trình độ</th>
                    <th>CV</th>
                    <th>Điểm CV</th>
                    <th>Trạng thái</th>
                    <th>Ứng tuyển</th>
                </tr></thead>
                <tbody>
                @forelse ($candidates as $candidate)
                    @php
                        $candidateId = data_get($candidate, 'MaUV')
                            ?? data_get($candidate, 'ma_uv')
                            ?? data_get($candidate, 'id')
                            ?? data_get($candidate, 'MaUngVien');
                        $name = data_get($candidate, 'HoTen', 'N/A');
                        $birthDate = data_get($candidate, 'NgaySinh');
                        $email = data_get($candidate, 'Email');
                        $phone = data_get($candidate, 'DienThoai');
                        $degree = data_get($candidate, 'TrinhDo');
                        $cvFile = data_get($candidate, 'FileCV');
                        $score = (int) data_get($candidate, 'DiemCV', 0);
                        $candidateStatus = $score >= 8 ? 'Rất tiềm năng' : ($score >= 6 ? 'Khá' : 'Cần xem lại');
                        $scoreClass = $score >= 8 ? 'score-high' : ($score >= 6 ? 'score-mid' : 'score-low');
                    @endphp
                    <tr @if($score >= 8) style="background:#edf8ef;" @endif>
                        <td>{{ $candidateId }}</td>
                        <td>{{ $name }}</td>
                        <td>{{ $birthDate ?: 'N/A' }}</td>
                        <td>{{ $email ?: 'Chưa có email' }}</td>
                        <td>{{ $phone ?: 'Chưa có số điện thoại' }}</td>
                        <td>{{ $degree ?: 'Chưa cập nhật' }}</td>
                        <td>
                            @if (!empty($cvFile))
                                <a class="btn btn-secondary" href="{{ route('legacy.upload', ['path' => 'cv/' . ltrim((string) $cvFile, '/')]) }}" target="_blank">Xem CV</a>
                            @else
                                <span class="muted">Chưa có CV</span>
                            @endif
                        </td>
                        <td><strong class="{{ $scoreClass ?? '' }}">{{ $score ?? 0 }}</strong></td>
                        <td><strong class="{{ $scoreClass ?? '' }}">{{ $candidateStatus ?? '' }}</strong></td>
                        <td class="nowrap-cell">
                            @if (in_array('them_ho_so', $permissions, true) && !empty($candidateId))
                                <a class="btn" href="{{ route('tuyendung.ungvien.apply', ['candidate' => $candidateId]) }}">Nộp hồ sơ</a>
                            @elseif (in_array('them_ho_so', $permissions, true))
                                <span class="muted">Thiếu mã ứng viên</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="muted">Không có ứng viên phù hợp.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="top-gap-lg">{{ $candidates->links() }}</div>
    </section>
@endsection