<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;

use App\Services\RecruitmentService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    public function __construct(private RecruitmentService $recruitmentService)
    {
    }

    public function index(Request $request): View
    {
        $campaigns = $this->recruitmentService->paginate($request->only(['q', 'status']));
        $campaigns->appends($request->query());

        return view('tuyendung.index', [
            'campaigns' => $campaigns,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('tuyendung.form', ['mode' => 'create', 'campaign' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        try {
            $campaignId = $this->recruitmentService->create($payload);

            return redirect()->route('tuyendung.edit', ['recruitment' => $campaignId])
                ->with('success', 'Da tao dot tuyen dung thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the tao dot tuyen dung: ' . $exception->getMessage()]);
        }
    }

    public function edit(int $recruitment): View
    {
        $campaign = $this->recruitmentService->find($recruitment);
        abort_if($campaign === null, 404);

        return view('tuyendung.form', ['mode' => 'edit', 'campaign' => $campaign]);
    }

    public function update(Request $request, int $recruitment): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        try {
            $this->recruitmentService->update($recruitment, $payload);

            return redirect()->route('tuyendung.edit', ['recruitment' => $recruitment])
                ->with('success', 'Da cap nhat dot tuyen dung thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the cap nhat dot tuyen dung: ' . $exception->getMessage()]);
        }
    }

    public function destroy(int $recruitment): RedirectResponse
    {
        try {
            $this->recruitmentService->delete($recruitment);

            return redirect()->route('tuyendung.index')
                ->with('success', 'Da xoa dot tuyen dung thanh cong.');
        } catch (QueryException $exception) {
            return back()->withErrors(['form' => 'Khong the xoa dot tuyen dung.']);
        }
    }

    public function destroyLegacy(int $recruitment): RedirectResponse
    {
        return $this->destroy($recruitment);
    }

    public function candidates(Request $request): View
    {
        $candidates = $this->recruitmentService->paginateCandidates($request->only(['q', 'score']));
        $candidates->appends($request->query());

        return view('tuyendung.candidates', [
            'candidates' => $candidates,
            'filters' => $request->only(['q', 'score']),
        ]);
    }

    public function createCandidate(): View
    {
        return view('tuyendung.candidate_form');
    }

    public function storeCandidate(Request $request): RedirectResponse
    {
        $payload = $this->validateCandidatePayload($request);

        // Handle CV file upload
        if ($request->hasFile('FileCVUpload') && $request->file('FileCVUpload')->isValid()) {
            $file = $request->file('FileCVUpload');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $uploadDir = base_path('../uploads/cv');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file->move($uploadDir, $filename);
            $payload['FileCV'] = $filename;
        }

        try {
            $candidateId = $this->recruitmentService->createCandidate($payload);

            return redirect()->route('tuyendung.ungvien.apply', ['candidate' => $candidateId])
                ->with('success', 'Da them ung vien thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the tao ung vien: ' . $exception->getMessage()]);
        }
    }

    public function applyCandidate(int $candidate): View
    {
        $candidateRecord = $this->recruitmentService->findCandidate($candidate);
        abort_if($candidateRecord === null, 404);

        return view('tuyendung.apply', [
            'candidate' => $candidateRecord,
            'campaigns' => $this->recruitmentService->campaignOptions(),
        ]);
    }

    public function attachCandidate(Request $request, int $candidate): RedirectResponse
    {
        $candidateRecord = $this->recruitmentService->findCandidate($candidate);
        abort_if($candidateRecord === null, 404);

        $payload = validator([
            'MaDTD' => $request->input('MaDTD', $request->input('campaign_id')),
            'GhiChu' => $request->input('GhiChu', $request->input('ghichu')),
        ], [
            'MaDTD' => ['required', 'integer', 'min:1'],
            'GhiChu' => ['nullable', 'string'],
        ])->validate();

        try {
            $applicationId = $this->recruitmentService->attachCandidate((int) $payload['MaDTD'], [
                'campaign_id' => (int) $payload['MaDTD'],
                'candidate_id' => $candidate,
                'note' => $payload['GhiChu'] ?? null,
            ]);

            return redirect()->route('tuyendung.hoso.phongvan', ['application' => $applicationId])
                ->with('success', 'Da tao ho so ung tuyen thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the tao ho so ung tuyen: ' . $exception->getMessage()]);
        }
    }

    public function applications(Request $request, int $recruitment): View
    {
        $campaign = $this->recruitmentService->find($recruitment);
        abort_if($campaign === null, 404);

        $applications = $this->recruitmentService->paginateApplications($recruitment, $request->only(['q', 'status']));
        $applications->appends($request->query());

        return view('tuyendung.applications', [
            'campaign' => $campaign,
            'applications' => $applications,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function updateApplicationStatus(Request $request, int $application): RedirectResponse
    {
        $applicationRecord = $this->recruitmentService->findApplication($application);
        abort_if($applicationRecord === null, 404);

        $payload = validator([
            'TrangThai' => $request->input('TrangThai', $request->input('status')),
            'GhiChu' => $request->input('GhiChu', $request->input('ghichu')),
        ], [
            'TrangThai' => ['required', 'in:Nộp hồ sơ,Sàng lọc,Phỏng vấn,Offer,Nhận việc,Rớt'],
            'GhiChu' => ['nullable', 'string'],
        ])->validate();

        try {
            $this->recruitmentService->updateApplicationStatus($application, (string) $payload['TrangThai']);

            return redirect()->route('tuyendung.hoso.index', ['recruitment' => $applicationRecord['MaDTD']])
                ->with('success', 'Da cap nhat trang thai ho so.');
        } catch (QueryException $exception) {
            return back()->withErrors(['form' => 'Khong the cap nhat trang thai: ' . $exception->getMessage()]);
        }
    }

    public function interviews(int $application): View
    {
        $applicationRecord = $this->recruitmentService->findApplication($application);
        abort_if($applicationRecord === null, 404);

        return view('tuyendung.interviews', [
            'application' => $applicationRecord,
            'interviews' => $this->recruitmentService->listInterviews($application),
            'reviews' => $this->recruitmentService->listReviews($application),
        ]);
    }

    public function storeInterview(Request $request, int $application): RedirectResponse
    {
        $applicationRecord = $this->recruitmentService->findApplication($application);
        abort_if($applicationRecord === null, 404);

        $payload = validator([
            'NgayPhongVan' => $request->input('NgayPhongVan', $request->input('ngay')),
            'GioPhongVan' => $request->input('GioPhongVan', $request->input('gio')),
            'DiaDiem' => $request->input('DiaDiem', $request->input('diadiem')),
            'GhiChu' => $request->input('GhiChu', $request->input('ghichu')),
            'KetQua' => $request->input('KetQua', $request->input('ketqua')),
        ], [
            'NgayPhongVan' => ['required', 'date'],
            'GioPhongVan' => ['required', 'date_format:H:i'],
            'DiaDiem' => ['nullable', 'string', 'max:255'],
            'GhiChu' => ['nullable', 'string'],
            'KetQua' => ['nullable', 'string', 'max:50'],
        ])->validate();

        try {
            $this->recruitmentService->storeInterview($application, $payload);

            return redirect()->route('tuyendung.hoso.phongvan', ['application' => $application])
                ->with('success', 'Da them lich phong van.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the them lich phong van: ' . $exception->getMessage()]);
        }
    }

    public function storeReview(Request $request, int $application): RedirectResponse
    {
        $applicationRecord = $this->recruitmentService->findApplication($application);
        abort_if($applicationRecord === null, 404);

        $payload = validator([
            'DiemKyNang' => $request->input('DiemKyNang', $request->input('kynang')),
            'DiemKinhNghiem' => $request->input('DiemKinhNghiem', $request->input('kinhnghiem')),
            'DiemThaiDo' => $request->input('DiemThaiDo', $request->input('thaido')),
            'NhanXet' => $request->input('NhanXet', $request->input('nhanxet')),
        ], [
            'DiemKyNang' => ['required', 'integer', 'min:1', 'max:10'],
            'DiemKinhNghiem' => ['required', 'integer', 'min:1', 'max:10'],
            'DiemThaiDo' => ['required', 'integer', 'min:1', 'max:10'],
            'NhanXet' => ['nullable', 'string'],
        ])->validate();

        try {
            $this->recruitmentService->storeReview($application, $payload);

            return redirect()->route('tuyendung.hoso.phongvan', ['application' => $application])
                ->with('success', 'Da luu danh gia phong van.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the luu danh gia: ' . $exception->getMessage()]);
        }
    }

    public function updateKanban(Request $request): Response
    {
        $payload = validator([
            'MaHS' => $request->input('MaHS'),
            'TrangThai' => $request->input('TrangThai'),
        ], [
            'MaHS' => ['required', 'integer', 'min:1'],
            'TrangThai' => ['required', 'in:Nộp hồ sơ,Sàng lọc,Phỏng vấn,Offer,Nhận việc,Rớt'],
        ])->validate();

        try {
            $this->recruitmentService->updateKanban((int) $payload['MaHS'], [
                'TrangThai' => $payload['TrangThai'],
                'GhiChu' => null,
            ]);

            return response('ok', 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
        } catch (\Throwable) {
            return response('error', 422, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'TenDotTuyenDung' => ['required', 'string', 'max:200'],
            'ViTriTuyenDung' => ['required', 'string', 'max:150'],
            'SoLuong' => ['required', 'integer', 'min:1'],
            'TuNgay' => ['required', 'date'],
            'DenNgay' => ['nullable', 'date'],
            'TrangThai' => ['required', 'in:Đang tuyển,Đã kết thúc'],
            'MoTa' => ['nullable', 'string'],
        ]);
    }

    private function validateCandidatePayload(Request $request): array
    {
        $payload = [
            'HoTen' => $request->input('HoTen', $request->input('hoten')),
            'NgaySinh' => $request->input('NgaySinh', $request->input('ngaysinh')),
            'GioiTinh' => $request->input('GioiTinh', $request->input('gioitinh')),
            'Email' => $request->input('Email', $request->input('email')),
            'DienThoai' => $request->input('DienThoai', $request->input('dienthoai')),
            'TrinhDo' => $request->input('TrinhDo', $request->input('trinhdo')),
            'KinhNghiem' => $request->input('KinhNghiem', $request->input('kinhnghiem')),
        ];

        return validator($payload, [
            'HoTen' => ['required', 'string', 'max:100'],
            'NgaySinh' => ['nullable', 'date'],
            'GioiTinh' => ['nullable', 'in:Nam,Nữ'],
            'Email' => ['nullable', 'email', 'max:100'],
            'DienThoai' => ['nullable', 'string', 'max:20'],
            'TrinhDo' => ['nullable', 'string', 'max:100'],
            'KinhNghiem' => ['nullable', 'string'],
        ])->validate();
    }
}