<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceGatewayController;
use App\Http\Controllers\Api\AccountBizController;
use App\Http\Controllers\Api\AuditLogBizController;
use App\Http\Controllers\Api\AttendanceBizController;
use App\Http\Controllers\Api\ChatbotBizController;
use App\Http\Controllers\Api\ContractBizController;
use App\Http\Controllers\Api\DashboardBizController;
use App\Http\Controllers\Api\DepartmentBizController;
use App\Http\Controllers\Api\EmployeeBizController;
use App\Http\Controllers\Api\EmployeeProfileBizController;
use App\Http\Controllers\Api\InsuranceBizController;
use App\Http\Controllers\Api\LeaveRequestBizController;
use App\Http\Controllers\Api\PayrollBizController;
use App\Http\Controllers\Api\PermissionBizController;
use App\Http\Controllers\Api\RecruitmentBizController;
use App\Http\Controllers\Api\ReportBizController;
use App\Http\Controllers\Api\RolePermissionBizController;
use App\Http\Controllers\Api\SearchBizController;
use App\Http\Controllers\Api\SystemHealthBizController;
use App\Http\Controllers\Api\TrainingBizController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('services')->middleware('api.token')->group(function () {
    Route::get('/', [ServiceGatewayController::class, 'catalog']);
    Route::get('{service}/{resource}/meta', [ServiceGatewayController::class, 'meta']);
    Route::get('{service}/{resource}/export', [ServiceGatewayController::class, 'export']);
    Route::get('{service}/{resource}', [ServiceGatewayController::class, 'index']);
    Route::get('{service}/{resource}/{id}', [ServiceGatewayController::class, 'show']);
    Route::post('{service}/{resource}', [ServiceGatewayController::class, 'store']);
    Route::put('{service}/{resource}/{id}', [ServiceGatewayController::class, 'update']);
    Route::delete('{service}/{resource}/{id}', [ServiceGatewayController::class, 'destroy']);
});

foreach (array_keys(config('service_registry.services', [])) as $serviceAlias) {
    Route::prefix($serviceAlias)->middleware('api.token')->group(function () use ($serviceAlias) {
        Route::get('/', [ServiceGatewayController::class, 'serviceCatalog'])->defaults('service', $serviceAlias);
        Route::get('{resource}/meta', [ServiceGatewayController::class, 'aliasMeta'])->defaults('service', $serviceAlias);
        Route::get('{resource}/export', [ServiceGatewayController::class, 'aliasExport'])->defaults('service', $serviceAlias);
        Route::get('{resource}', [ServiceGatewayController::class, 'aliasIndex'])->defaults('service', $serviceAlias);
        Route::get('{resource}/{id}', [ServiceGatewayController::class, 'aliasShow'])->defaults('service', $serviceAlias);
        Route::post('{resource}', [ServiceGatewayController::class, 'aliasStore'])->defaults('service', $serviceAlias);
        Route::put('{resource}/{id}', [ServiceGatewayController::class, 'aliasUpdate'])->defaults('service', $serviceAlias);
        Route::delete('{resource}/{id}', [ServiceGatewayController::class, 'aliasDestroy'])->defaults('service', $serviceAlias);
    });
}

// ─── Internal Business API (/api/biz/*) ──────────────────────────────────────
Route::prefix('biz')->middleware('api.token')->group(function () {

    // Dashboard
    Route::get('dashboard/metrics',                        [DashboardBizController::class, 'metrics']);
    Route::get('dashboard/recent-activity',                [DashboardBizController::class, 'recentActivity']);
    Route::post('dashboard/charts',                        [DashboardBizController::class, 'charts']);
    Route::post('dashboard/notifications/mark-read',       [DashboardBizController::class, 'markNotificationsRead']);

    // Employees
    Route::post('employees/paginate',         [EmployeeBizController::class, 'paginate']);
    Route::get('employees/options',           [EmployeeBizController::class, 'options']);
    Route::get('employees/salary-grades',     [EmployeeBizController::class, 'salaryGrades']);
    Route::post('employees',                  [EmployeeBizController::class, 'store']);
    Route::get('employees/{id}',              [EmployeeBizController::class, 'show']);
    Route::put('employees/{id}',              [EmployeeBizController::class, 'update']);
    Route::delete('employees/{id}',           [EmployeeBizController::class, 'destroy']);

    // Departments
    Route::post('departments/paginate', [DepartmentBizController::class, 'paginate']);
    Route::get('departments/{id}',      [DepartmentBizController::class, 'show']);

    // Attendance
    Route::post('attendance/paginate',         [AttendanceBizController::class, 'paginate']);
    Route::get('attendance/employee-options',  [AttendanceBizController::class, 'employeeOptions']);
    Route::get('attendance/export-rows',       [AttendanceBizController::class, 'exportRows']);
    Route::get('attendance/worked-days',       [AttendanceBizController::class, 'workedDays']);
    Route::get('attendance/monthly-matrix',    [AttendanceBizController::class, 'monthlyMatrix']);
    Route::post('attendance',                  [AttendanceBizController::class, 'store']);
    Route::get('attendance/{id}',              [AttendanceBizController::class, 'show']);
    Route::put('attendance/{id}',              [AttendanceBizController::class, 'update']);
    Route::delete('attendance/{id}',           [AttendanceBizController::class, 'destroy']);

    // Payroll
    Route::post('payroll/paginate',         [PayrollBizController::class, 'paginate']);
    Route::get('payroll/employee-options',  [PayrollBizController::class, 'employeeOptions']);
    Route::post('payroll/run-monthly',      [PayrollBizController::class, 'runMonthly']);
    Route::get('payroll/export',            [PayrollBizController::class, 'export']);
    Route::post('payroll',                  [PayrollBizController::class, 'store']);
    Route::get('payroll/{id}',              [PayrollBizController::class, 'show']);
    Route::put('payroll/{id}',              [PayrollBizController::class, 'update']);
    Route::post('payroll/{id}/lock',        [PayrollBizController::class, 'lock']);
    Route::post('payroll/{id}/unlock',      [PayrollBizController::class, 'unlock']);

    // Recruitment – campaigns
    Route::post('recruitment/paginate',                            [RecruitmentBizController::class, 'paginate']);
    Route::get('recruitment/campaign-options',                     [RecruitmentBizController::class, 'campaignOptions']);
    Route::post('recruitment',                                     [RecruitmentBizController::class, 'store']);
    Route::get('recruitment/{id}',                                 [RecruitmentBizController::class, 'show']);
    Route::put('recruitment/{id}',                                 [RecruitmentBizController::class, 'update']);
    Route::delete('recruitment/{id}',                              [RecruitmentBizController::class, 'destroy']);
    // Candidates
    Route::post('recruitment/candidates/paginate',                 [RecruitmentBizController::class, 'paginateCandidates']);
    Route::post('recruitment/candidates',                          [RecruitmentBizController::class, 'storeCandidate']);
    Route::get('recruitment/candidates/{id}',                      [RecruitmentBizController::class, 'showCandidate']);
    // Applications
    Route::post('recruitment/{campaignId}/applications/paginate',  [RecruitmentBizController::class, 'paginateApplications']);
    Route::post('recruitment/{campaignId}/applications',           [RecruitmentBizController::class, 'attachCandidate']);
    Route::get('recruitment/applications/{id}',                    [RecruitmentBizController::class, 'showApplication']);
    Route::put('recruitment/applications/{id}/status',             [RecruitmentBizController::class, 'updateApplicationStatus']);
    Route::put('recruitment/applications/{id}/kanban',             [RecruitmentBizController::class, 'updateKanban']);
    // Interviews & reviews
    Route::get('recruitment/applications/{id}/interviews',         [RecruitmentBizController::class, 'listInterviews']);
    Route::get('recruitment/applications/{id}/reviews',            [RecruitmentBizController::class, 'listReviews']);
    Route::post('recruitment/applications/{id}/interviews',        [RecruitmentBizController::class, 'storeInterview']);
    Route::post('recruitment/interviews/{id}/reviews',             [RecruitmentBizController::class, 'storeReview']);

    // Training
    Route::post('training/paginate',                     [TrainingBizController::class, 'paginate']);
    Route::post('training',                              [TrainingBizController::class, 'store']);
    Route::get('training/{id}',                          [TrainingBizController::class, 'show']);
    Route::put('training/{id}',                          [TrainingBizController::class, 'update']);
    Route::delete('training/{id}',                       [TrainingBizController::class, 'destroy']);
    Route::get('training/{id}/participants-page',        [TrainingBizController::class, 'participantsPageData']);
    Route::post('training/{id}/participants',            [TrainingBizController::class, 'addParticipant']);
    Route::put('training/participants/{participantId}',  [TrainingBizController::class, 'updateParticipantResult']);

    // Reports
    Route::post('reports/paginate', [ReportBizController::class, 'paginate']);
    Route::get('reports/export',    [ReportBizController::class, 'export']);
    Route::post('reports',          [ReportBizController::class, 'store']);
    Route::get('reports/{id}',      [ReportBizController::class, 'show']);
    Route::put('reports/{id}',      [ReportBizController::class, 'update']);
    Route::delete('reports/{id}',   [ReportBizController::class, 'destroy']);

    // Contracts
    Route::get('contracts/{id}',             [ContractBizController::class, 'show']);
    Route::get('contracts/{id}/salary-history', [ContractBizController::class, 'salaryHistory']);
    Route::post('contracts/{id}/renew',      [ContractBizController::class, 'renew']);
    Route::post('contracts/{id}/terminate',  [ContractBizController::class, 'terminate']);

    // Employee profiles
    Route::get('employee-profiles/pending-requests',         [EmployeeProfileBizController::class, 'pendingRequests']);
    Route::post('employee-profiles/requests/{id}/resolve',   [EmployeeProfileBizController::class, 'resolveRequest']);
    Route::get('employee-profiles/{id}',                     [EmployeeProfileBizController::class, 'show']);
    Route::get('employee-profiles/employee/{eid}/info',      [EmployeeProfileBizController::class, 'employeeInfo']);

    // Accounts
    Route::get('accounts/by-username',          [AccountBizController::class, 'showByUsername']);
    Route::get('accounts/check-username',        [AccountBizController::class, 'checkUsernameAvailable']);
    Route::get('accounts/{id}',                  [AccountBizController::class, 'show']);
    Route::patch('accounts/{id}/username',       [AccountBizController::class, 'updateUsername']);
    Route::patch('accounts/{id}/password',       [AccountBizController::class, 'updatePassword']);
    Route::get('accounts/{id}/sessions',         [AccountBizController::class, 'listSessions']);
    Route::get('accounts/employee-for-account',  [AccountBizController::class, 'findEmployeeForAccount']);
    // Session audit
    Route::post('accounts/sessions/register',    [AccountBizController::class, 'registerSession']);
    Route::post('accounts/sessions/touch',       [AccountBizController::class, 'touchSession']);
    Route::post('accounts/sessions/revoke-others', [AccountBizController::class, 'revokeOtherSessions']);
    Route::post('accounts/sessions/revoke',      [AccountBizController::class, 'revokeCurrentSession']);
    Route::get('accounts/sessions/is-revoked',   [AccountBizController::class, 'isSessionRevoked']);
    // Password reset
    Route::post('accounts/reset-token',          [AccountBizController::class, 'createResetToken']);
    Route::get('accounts/reset-token/find',      [AccountBizController::class, 'findValidResetToken']);
    Route::post('accounts/reset-token/{id}/used', [AccountBizController::class, 'markResetTokenUsed']);

    // Role permissions
    Route::get('role-permissions',                       [RolePermissionBizController::class, 'indexData']);
    Route::get('role-permissions/accounts/{id}',         [RolePermissionBizController::class, 'accountDetail']);
    Route::get('role-permissions/roles',                 [RolePermissionBizController::class, 'listRoles']);
    Route::post('role-permissions/roles',                [RolePermissionBizController::class, 'storeRole']);
    Route::delete('role-permissions/roles/{id}',         [RolePermissionBizController::class, 'destroyRole']);
    Route::put('role-permissions/roles/{id}/permissions', [RolePermissionBizController::class, 'updateRolePermissions']);
    Route::post('role-permissions/assign',               [RolePermissionBizController::class, 'assignAccountRole']);
    Route::post('role-permissions/revoke',               [RolePermissionBizController::class, 'revokeAccountRole']);
    Route::post('role-permissions/roles/{id}/restore-defaults', [RolePermissionBizController::class, 'restoreDefaultPermissions']);

    // Permissions
    Route::get('permissions', [PermissionBizController::class, 'byAccount']);

    // Chatbot
    Route::post('chatbot/paginate',               [ChatbotBizController::class, 'paginate']);
    Route::post('chatbot/sessions/upsert',        [ChatbotBizController::class, 'upsertSession']);
    Route::post('chatbot/messages',               [ChatbotBizController::class, 'logMessage']);
    Route::post('chatbot/drafts',                 [ChatbotBizController::class, 'createDraft']);
    Route::get('chatbot/drafts/pending',          [ChatbotBizController::class, 'getPendingDraft']);
    Route::patch('chatbot/drafts/{id}/status',    [ChatbotBizController::class, 'updateDraftStatus']);
    Route::post('chatbot/execute-draft',          [ChatbotBizController::class, 'executeDraft']);
    Route::get('chatbot/{id}',                    [ChatbotBizController::class, 'show']);

    // Leave requests
    Route::post('leave-requests/{id}/approve',    [LeaveRequestBizController::class, 'approve']);
    Route::post('leave-requests/{id}/reject',     [LeaveRequestBizController::class, 'reject']);
    Route::post('leave-requests',                 [LeaveRequestBizController::class, 'create']);

    // Insurances
    Route::post('insurances/{id}/deactivate',     [InsuranceBizController::class, 'deactivate']);

    // Search
    Route::get('search', [SearchBizController::class, 'index']);

    // Audit log
    Route::get('audit-log', [AuditLogBizController::class, 'index']);

    // System health
    Route::get('system-health/status', [SystemHealthBizController::class, 'status']);
    Route::post('system-health/run-checks', [SystemHealthBizController::class, 'runChecks']);
});
