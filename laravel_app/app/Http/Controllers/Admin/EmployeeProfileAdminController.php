<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Services\EmployeeProfileAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;

class EmployeeProfileAdminController extends Controller
{
    public function __construct(private EmployeeProfileAdminService $profiles)
    {
    }

    public function show(int $profile): View
    {
        $record = $this->profiles->profileDetail($profile);
        abort_if($record === null, 404);

        return view('hosocanhan.show', ['profile' => $record]);
    }

    public function reviewRequests(Request $request): View
    {
        abort_unless($this->isAdminOrManager($request), 403);

        return view('hosocanhan.review_requests', [
            'requests' => $this->profiles->pendingRequests(),
        ]);
    }

    public function resolveRequest(Request $request, int $reviewRequest): RedirectResponse
    {
        abort_unless($this->isAdminOrManager($request), 403);

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'review_note' => ['nullable', 'string'],
        ]);

        try {
            $this->profiles->resolveRequest(
                $reviewRequest,
                (string) $validated['decision'],
                (int) $request->session()->get('MaTK', 0),
                (string) ($validated['review_note'] ?? '')
            );
        } catch (LogicException $exception) {
            return redirect()->route('hosocanhan.review-requests')->withErrors($exception->getMessage());
        }

        return redirect()->route('hosocanhan.review-requests')->with('success', 'Da xu ly yeu cau cap nhat ho so.');
    }

    public function employeeInfo(Request $request): JsonResponse
    {
        $employeeId = (int) $request->query('MaNV', 0);
        if ($employeeId <= 0) {
            return response()->json([], 400);
        }

        return response()->json($this->profiles->employeeInfo($employeeId) ?? []);
    }

    private function isAdminOrManager(Request $request): bool
    {
        $role = strtolower(trim((string) data_get($request->session()->get('taikhoan', []), 'VaiTro', '')));

        return in_array($role, ['admin', 'quanly'], true);
    }
}