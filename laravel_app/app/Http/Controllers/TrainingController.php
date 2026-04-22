<?php

namespace App\Http\Controllers;

use App\Services\TrainingService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function __construct(private TrainingService $trainingService)
    {
    }

    public function index(Request $request): View
    {
        $courses = $this->trainingService->paginate($request->only(['q', 'status']));
        $courses->appends($request->query());

        return view('daotao.index', [
            'courses' => $courses,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('daotao.form', ['mode' => 'create', 'course' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        try {
            $courseId = $this->trainingService->create($payload);

            return redirect()->route('daotao.edit', ['training' => $courseId])
                ->with('success', 'Da tao khoa dao tao thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the tao khoa dao tao: ' . $exception->getMessage()]);
        }
    }

    public function edit(int $training): View
    {
        $course = $this->trainingService->find($training);
        abort_if($course === null, 404);

        return view('daotao.form', ['mode' => 'edit', 'course' => $course]);
    }

    public function update(Request $request, int $training): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        try {
            $this->trainingService->update($training, $payload);

            return redirect()->route('daotao.edit', ['training' => $training])
                ->with('success', 'Da cap nhat khoa dao tao thanh cong.');
        } catch (QueryException $exception) {
            return back()->withInput()->withErrors(['form' => 'Khong the cap nhat khoa dao tao: ' . $exception->getMessage()]);
        }
    }

    public function destroy(int $training): RedirectResponse
    {
        try {
            $this->trainingService->delete($training);

            return redirect()->route('daotao.index')
                ->with('success', 'Da xoa khoa dao tao thanh cong.');
        } catch (QueryException $exception) {
            return back()->withErrors(['form' => 'Khong the xoa khoa dao tao.']);
        }
    }

    public function participants(int $training): View
    {
        $data = $this->trainingService->participantsPageData($training);
        abort_if($data['course'] === null, 404);

        return view('daotao.participants', $data);
    }

    public function storeParticipant(Request $request, int $training): RedirectResponse
    {
        $validated = $request->validate([
            'MaNV' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $created = $this->trainingService->addParticipant($training, (int) $validated['MaNV']);

            if (!$created) {
                return redirect()->route('daotao.hocvien', ['training' => $training])
                    ->withErrors(['form' => 'Nhan vien nay da co trong khoa dao tao.']);
            }

            return redirect()->route('daotao.hocvien', ['training' => $training])
                ->with('success', 'Da them nhan vien vao khoa dao tao.');
        } catch (QueryException $exception) {
            return redirect()->route('daotao.hocvien', ['training' => $training])
                ->withErrors(['form' => 'Khong the them nhan vien vao khoa dao tao.']);
        }
    }

    public function updateParticipantResult(Request $request, int $participant): RedirectResponse
    {
        $validated = $request->validate([
            'MaKDT' => ['required', 'integer', 'min:1'],
            'KetQua' => ['required', 'in:Đạt,Không đạt,Chưa đánh giá'],
            'DiemDanhGia' => ['nullable', 'numeric', 'between:0,10'],
            'GhiChu' => ['nullable', 'string'],
        ]);

        try {
            $this->trainingService->updateParticipantResult($participant, [
                'KetQua' => $validated['KetQua'],
                'DiemDanhGia' => $validated['DiemDanhGia'] ?? null,
                'GhiChu' => $validated['GhiChu'] ?? null,
            ]);

            return redirect()->route('daotao.hocvien', ['training' => (int) $validated['MaKDT']])
                ->with('success', 'Da cap nhat ket qua dao tao.');
        } catch (QueryException $exception) {
            return redirect()->route('daotao.hocvien', ['training' => (int) $validated['MaKDT']])
                ->withErrors(['form' => 'Khong the cap nhat ket qua dao tao.']);
        }
    }

    private function validatePayload(Request $request): array
    {
        $payload = $request->validate([
            'TenKhoaDaoTao' => ['required', 'string', 'max:200'],
            'TuNgay' => ['required', 'date'],
            'DenNgay' => ['required', 'date'],
            'NoiDung' => ['nullable', 'string'],
            'DonViToChuc' => ['nullable', 'string', 'max:150'],
            'TrangThai' => ['required', 'in:Lên kế hoạch,Đang đào tạo,Hoàn thành'],
        ]);

        if (strtotime((string) $payload['DenNgay']) < strtotime((string) $payload['TuNgay'])) {
            throw ValidationException::withMessages([
                'DenNgay' => 'Den ngay phai lon hon hoac bang tu ngay.',
            ]);
        }

        return $payload;
    }
}