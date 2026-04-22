<?php

namespace App\Http\Controllers;

use App\Services\ContractAdminService;
use App\Services\GenericResourceModuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use LogicException;

class ContractAdminController extends Controller
{
    public function __construct(
        private ContractAdminService $contracts,
        private GenericResourceModuleService $modules,
    )
    {
    }

    public function renewForm(int $contract): View
    {
        $contractData = $this->contracts->contractDetail($contract);
        abort_if($contractData === null, 404);

        return view('hopdong.renew', ['contract' => $contractData]);
    }

    public function renewStore(Request $request, int $contract): RedirectResponse
    {
        $payload = $request->validate([
            'SoHopDong' => ['required', 'string', 'max:50'],
            'LoaiHopDong' => ['required', 'in:Thử việc,Xác định thời hạn,Không xác định thời hạn'],
            'NgayBatDau' => ['required', 'date'],
            'NgayKetThuc' => ['nullable', 'date'],
            'GhiChu' => ['nullable', 'string'],
        ]);

        if (!empty($payload['NgayKetThuc']) && strtotime((string) $payload['NgayKetThuc']) < strtotime((string) $payload['NgayBatDau'])) {
            throw ValidationException::withMessages(['NgayKetThuc' => 'Ngay ket thuc khong duoc truoc ngay bat dau.']);
        }

        try {
            $this->contracts->renewContract($contract, $payload);
        } catch (LogicException $exception) {
            return back()->withInput()->withErrors(['form' => $exception->getMessage()]);
        }

        return redirect()->route('hopdong.index')->with('success', 'Da gia han hop dong thanh cong.');
    }

    public function terminate(int $contract): RedirectResponse
    {
        $this->contracts->terminateContract($contract);

        return redirect()->route('hopdong.index')->with('success', 'Da cham dut hop dong.');
    }

    public function salaryHistory(int $contract): View
    {
        $contractData = $this->contracts->contractDetail($contract);
        abort_if($contractData === null, 404);

        return view('hopdong.salary_history', [
            'contract' => $contractData,
            'history' => $this->contracts->salaryHistory($contract),
        ]);
    }

    public function destroyLegacy(int $contract): RedirectResponse
    {
        $this->modules->delete('contracts', (string) $contract);

        return redirect()->route('hopdong.index')->with('success', 'Da xoa hop dong thanh cong.');
    }
}