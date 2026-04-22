<?php

namespace App\Http\Controllers;

use App\Services\RolePermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    public function __construct(private RolePermissionService $rolePermissionService)
    {
    }

    public function index(): View
    {
        $data = $this->rolePermissionService->indexData();

        return view('phanquyen.index', [
            'roles' => $data['roles'],
            'groupOrder' => $data['groupOrder'],
            'groupedFunctions' => $this->rolePermissionService->groupFunctions($data['functions']),
            'permissionsByRole' => $data['permissionsByRole'],
        ]);
    }

    public function showAccount(int $account): View
    {
        return view('phanquyen.detail', $this->rolePermissionService->accountDetail($account));
    }

    public function update(Request $request, int $role): RedirectResponse
    {
        $validated = $request->validate([
            'chucnang' => ['nullable', 'array'],
            'chucnang.*' => ['integer'],
        ]);

        $this->rolePermissionService->updateRolePermissions($role, $validated['chucnang'] ?? []);

        return redirect()->route('phanquyen.index')->with('success', 'Da cap nhat quyen cho vai tro.');
    }

    public function restoreDefaults(int $role): RedirectResponse
    {
        if (!$this->rolePermissionService->restoreDefaultPermissions($role)) {
            return redirect()->route('phanquyen.index')->withErrors('Khong tim thay bo quyen mac dinh cho vai tro.');
        }

        return redirect()->route('phanquyen.index')->with('success', 'Da khoi phuc quyen mac dinh cho vai tro.');
    }
}