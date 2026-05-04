<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordRecoveryController;
use App\Http\Controllers\Admin\AccountAdminController;
use App\Http\Controllers\Admin\AccountSettingsController;
use App\Http\Controllers\Admin\ContractAdminController;
use App\Http\Controllers\Admin\EmployeeProfileAdminController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Hr\AttendanceController;
use App\Http\Controllers\Hr\DepartmentController;
use App\Http\Controllers\Hr\EmployeeController;
use App\Http\Controllers\Hr\PayrollController;
use App\Http\Controllers\Hr\RecruitmentController;
use App\Http\Controllers\Hr\TrainingController;
use App\Http\Controllers\Report\AuditLogExportController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\System\ChatbotMonitorController;
use App\Http\Controllers\System\DashboardController;
use App\Http\Controllers\System\ResourceModuleController;
use App\Http\Controllers\System\SearchController;
use App\Http\Controllers\System\ServiceConsoleController;
use App\Http\Controllers\System\SystemHealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login.form');
});

Route::get('/uploads/{path}', function (string $path) {
    $filePath = base_path('../uploads/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    return response()->file($filePath);
})->name('legacy.upload')->where('path', '.+');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/forgot-password', [PasswordRecoveryController::class, 'showForgot'])->name('password.forgot');
Route::post('/forgot-password', [PasswordRecoveryController::class, 'handleForgot'])->name('password.forgot.submit');
Route::get('/reset-password', [PasswordRecoveryController::class, 'showReset'])->name('password.reset');
Route::post('/reset-password', [PasswordRecoveryController::class, 'handleReset'])->name('password.reset.submit');
Route::get('/force-password-change', [PasswordRecoveryController::class, 'showForcedChange'])->middleware('session.auth')->name('password.force');
Route::post('/force-password-change', [PasswordRecoveryController::class, 'handleForcedChange'])->middleware('session.auth')->name('password.force.submit');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('session.auth')
    ->name('logout');
Route::get('/logout', [AuthController::class, 'logoutBridge'])
    ->middleware('session.auth')
    ->name('logout.get');
Route::get('/logout-bridge', [AuthController::class, 'logoutBridge'])->middleware('session.auth')->name('logout.bridge');

Route::get('/settings', [AccountSettingsController::class, 'show'])
    ->middleware('session.auth')
    ->name('settings.show');
Route::post('/settings/username', [AccountSettingsController::class, 'updateUsername'])
    ->middleware('session.auth')
    ->name('settings.username');
Route::post('/settings/password', [AccountSettingsController::class, 'updatePassword'])
    ->middleware('session.auth')
    ->name('settings.password');
Route::post('/settings/refresh-session', [AccountSettingsController::class, 'refreshSession'])
    ->middleware('session.auth')
    ->name('settings.refresh-session');
Route::post('/settings/revoke-other-sessions', [AccountSettingsController::class, 'revokeOtherSessions'])
    ->middleware('session.auth')
    ->name('settings.revoke-other-sessions');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('session.auth')
    ->name('dashboard');
Route::post('/dashboard/notifications/read', [DashboardController::class, 'markNotificationsRead'])
    ->middleware('session.auth')
    ->name('dashboard.notifications.read');
Route::get('/dashboard/charts', [DashboardController::class, 'chartData'])
    ->middleware('session.auth')
    ->name('dashboard.charts');

Route::get('/employees', [EmployeeController::class, 'index'])
    ->middleware(['session.auth', 'permission:xem_nhanvien'])
    ->name('employees.index');

Route::get('/employees/salary-grades-by-band', [EmployeeController::class, 'salaryGradesByBand'])
    ->middleware(['session.auth', 'permission:xem_nhanvien'])
    ->name('employees.salary-grades-by-band');

Route::get('/employees/create', [EmployeeController::class, 'create'])
    ->middleware(['session.auth', 'permission:them_nhanvien'])
    ->name('employees.create');

Route::post('/employees', [EmployeeController::class, 'store'])
    ->middleware(['session.auth', 'permission:them_nhanvien'])
    ->name('employees.store');

Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])
    ->middleware(['session.auth', 'permission:sua_nhanvien'])
    ->name('employees.edit');

Route::put('/employees/{employee}', [EmployeeController::class, 'update'])
    ->middleware(['session.auth', 'permission:sua_nhanvien'])
    ->name('employees.update');

Route::post('/employees/{employee}', [EmployeeController::class, 'update'])
    ->middleware(['session.auth', 'permission:sua_nhanvien'])
    ->name('employees.update.legacy');

Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])
    ->middleware(['session.auth', 'permission:xoa_nhanvien'])
    ->name('employees.destroy');

Route::get('/departments', [DepartmentController::class, 'index'])
    ->middleware(['session.auth', 'permission:xem_phongban'])
    ->name('departments.index');

Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware(['session.auth', 'permission:xem_chamcong'])
    ->name('attendance.index');

Route::get('/attendance/matrix', [AttendanceController::class, 'matrix'])
    ->middleware(['session.auth', 'permission:xem_chamcong'])
    ->name('attendance.matrix');

Route::post('/attendance/matrix/cell', [AttendanceController::class, 'updateCell'])
    ->middleware(['session.auth', 'permission:them_chamcong'])
    ->name('attendance.matrix.cell');

Route::get('/attendance/worked-days', [AttendanceController::class, 'workedDays'])
    ->middleware(['session.auth', 'permission:xem_chamcong'])
    ->name('attendance.worked-days');

Route::get('/attendance/export-excel', [AttendanceController::class, 'exportExcel'])
    ->middleware(['session.auth', 'permission:xuat_bang_cham_cong'])
    ->name('attendance.export-excel');

Route::get('/attendance/create', [AttendanceController::class, 'create'])
    ->middleware(['session.auth', 'permission:them_chamcong'])
    ->name('attendance.create');

Route::post('/attendance', [AttendanceController::class, 'store'])
    ->middleware(['session.auth', 'permission:them_chamcong'])
    ->name('attendance.store');

Route::get('/attendance/{attendance}/edit', [AttendanceController::class, 'edit'])
    ->middleware(['session.auth', 'permission:sua_chamcong'])
    ->name('attendance.edit');

Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])
    ->middleware(['session.auth', 'permission:sua_chamcong'])
    ->name('attendance.update');

Route::post('/attendance/{attendance}', [AttendanceController::class, 'update'])
    ->middleware(['session.auth', 'permission:sua_chamcong'])
    ->name('attendance.update.legacy');

Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy'])
    ->middleware(['session.auth', 'permission:xoa_chamcong'])
    ->name('attendance.destroy');

Route::get('/payroll', [PayrollController::class, 'index'])
    ->middleware(['session.auth', 'permission:xem_luong'])
    ->name('payroll.index');

Route::post('/payroll/run-monthly', [PayrollController::class, 'runMonthly'])
    ->middleware(['session.auth', 'permission:tinh_luong_thang'])
    ->name('payroll.run-monthly');
Route::get('/payroll/job-status', [PayrollController::class, 'jobStatus'])
    ->middleware(['session.auth', 'permission:xem_luong'])
    ->name('payroll.job-status');

Route::get('/payroll/create', [PayrollController::class, 'create'])
    ->middleware(['session.auth', 'permission:tinh_luong_thang'])
    ->name('payroll.create');

Route::post('/payroll', [PayrollController::class, 'store'])
    ->middleware(['session.auth', 'permission:tinh_luong_thang'])
    ->name('payroll.store');

Route::get('/payroll/{payroll}/edit', [PayrollController::class, 'edit'])
    ->middleware(['session.auth', 'permission:mo_chot_luong'])
    ->name('payroll.edit');

Route::get('/payroll/{payroll}', [PayrollController::class, 'show'])
    ->middleware(['session.auth', 'permission:xem_luong'])
    ->name('payroll.show');

Route::put('/payroll/{payroll}', [PayrollController::class, 'update'])
    ->middleware(['session.auth', 'permission:mo_chot_luong'])
    ->name('payroll.update');

Route::post('/payroll/{payroll}', [PayrollController::class, 'update'])
    ->middleware(['session.auth', 'permission:mo_chot_luong'])
    ->name('payroll.update.legacy');

Route::get('/recruitment', [RecruitmentController::class, 'index'])
    ->middleware(['session.auth', 'permission:xem_dot_tuyen'])
    ->name('recruitment.index');

Route::get('/recruitment/candidates', [RecruitmentController::class, 'candidates'])
    ->middleware(['session.auth', 'permission:xem_ung_vien'])
    ->name('recruitment.candidates.index');

Route::get('/recruitment/candidates/create', [RecruitmentController::class, 'createCandidate'])
    ->middleware(['session.auth', 'permission:them_ung_vien'])
    ->name('recruitment.candidates.create');

Route::post('/recruitment/candidates', [RecruitmentController::class, 'storeCandidate'])
    ->middleware(['session.auth', 'permission:them_ung_vien'])
    ->name('recruitment.candidates.store');

Route::get('/recruitment/candidates/{candidate}/apply', [RecruitmentController::class, 'applyCandidate'])
    ->middleware(['session.auth', 'permission:them_ho_so'])
    ->name('recruitment.candidates.apply');

Route::post('/recruitment/candidates/{candidate}/apply', [RecruitmentController::class, 'attachCandidate'])
    ->middleware(['session.auth', 'permission:them_ho_so'])
    ->name('recruitment.candidates.attach');

Route::get('/recruitment/{recruitment}/applications', [RecruitmentController::class, 'applications'])
    ->middleware(['session.auth', 'permission:xem_ho_so'])
    ->name('recruitment.applications.index');

Route::post('/recruitment/applications/{application}/status', [RecruitmentController::class, 'updateApplicationStatus'])
    ->middleware(['session.auth', 'permission:capnhat_trangthai'])
    ->name('recruitment.applications.status');

Route::get('/recruitment/applications/{application}/interviews', [RecruitmentController::class, 'interviews'])
    ->middleware(['session.auth', 'permission:xem_lich_phong_van'])
    ->name('recruitment.applications.interviews');

Route::post('/recruitment/applications/{application}/interviews', [RecruitmentController::class, 'storeInterview'])
    ->middleware(['session.auth', 'permission:them_lich_phong_van'])
    ->name('recruitment.applications.interviews.store');

Route::post('/recruitment/applications/{application}/reviews', [RecruitmentController::class, 'storeReview'])
    ->middleware(['session.auth', 'permission:them_danh_gia'])
    ->name('recruitment.applications.reviews.store');

Route::post('/recruitment/applications/kanban-status', [RecruitmentController::class, 'updateKanban'])
    ->middleware(['session.auth', 'permission:capnhat_trangthai'])
    ->name('recruitment.applications.kanban-status');

Route::get('/recruitment/create', [RecruitmentController::class, 'create'])
    ->middleware(['session.auth', 'permission:them_dot_tuyen'])
    ->name('recruitment.create');

Route::post('/recruitment', [RecruitmentController::class, 'store'])
    ->middleware(['session.auth', 'permission:them_dot_tuyen'])
    ->name('recruitment.store');

Route::get('/recruitment/{recruitment}/edit', [RecruitmentController::class, 'edit'])
    ->middleware(['session.auth', 'permission:them_dot_tuyen'])
    ->name('recruitment.edit');

Route::put('/recruitment/{recruitment}', [RecruitmentController::class, 'update'])
    ->middleware(['session.auth', 'permission:them_dot_tuyen'])
    ->name('recruitment.update');

Route::post('/recruitment/{recruitment}', [RecruitmentController::class, 'update'])
    ->middleware(['session.auth', 'permission:them_dot_tuyen'])
    ->name('recruitment.update.legacy');

Route::delete('/recruitment/{recruitment}', [RecruitmentController::class, 'destroy'])
    ->middleware(['session.auth', 'permission:xoa_dot_tuyen'])
    ->name('recruitment.destroy');

Route::get('/training', [TrainingController::class, 'index'])
    ->middleware(['session.auth', 'permission:xem_khoa_dao_tao'])
    ->name('training.index');

Route::get('/training/create', [TrainingController::class, 'create'])
    ->middleware(['session.auth', 'permission:them_khoa_dao_tao'])
    ->name('training.create');

Route::post('/training', [TrainingController::class, 'store'])
    ->middleware(['session.auth', 'permission:them_khoa_dao_tao'])
    ->name('training.store');

Route::get('/training/{training}/edit', [TrainingController::class, 'edit'])
    ->middleware(['session.auth', 'permission:them_khoa_dao_tao'])
    ->name('training.edit');

Route::get('/training/{training}/participants', [TrainingController::class, 'participants'])
    ->middleware(['session.auth', 'permission:xem_tham_gia_dao_tao'])
    ->name('training.participants');

Route::post('/training/{training}/participants', [TrainingController::class, 'storeParticipant'])
    ->middleware(['session.auth', 'permission:them_tham_gia_dao_tao'])
    ->name('training.participants.store');

Route::post('/training/participants/{participant}/result', [TrainingController::class, 'updateParticipantResult'])
    ->middleware(['session.auth', 'permission:capnhat_ketqua_dao_tao'])
    ->name('training.participants.result');

Route::put('/training/{training}', [TrainingController::class, 'update'])
    ->middleware(['session.auth', 'permission:them_khoa_dao_tao'])
    ->name('training.update');

Route::post('/training/{training}', [TrainingController::class, 'update'])
    ->middleware(['session.auth', 'permission:them_khoa_dao_tao'])
    ->name('training.update.legacy');

Route::delete('/training/{training}', [TrainingController::class, 'destroy'])
    ->middleware(['session.auth', 'permission:xoa_khoa_dao_tao'])
    ->name('training.destroy');

Route::get('/training/{training}/delete-legacy', [TrainingController::class, 'destroy'])
    ->middleware(['session.auth', 'permission:xoa_khoa_dao_tao'])
    ->name('training.destroy.legacy');

Route::get('/reports', [ReportController::class, 'index'])
    ->middleware(['session.auth', 'permission:xem_baocao'])
    ->name('reports.index');

Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])
    ->middleware(['session.auth', 'permission:xuatex_baocao'])
    ->name('reports.export-excel');

Route::get('/reports/export-json', [ReportController::class, 'exportJson'])
    ->middleware(['session.auth', 'permission:xuatex_baocao'])
    ->name('reports.export-json');

Route::get('/reports/create', [ReportController::class, 'create'])
    ->middleware(['session.auth', 'permission:them_baocao'])
    ->name('reports.create');

Route::post('/reports', [ReportController::class, 'store'])
    ->middleware(['session.auth', 'permission:them_baocao'])
    ->name('reports.store');

Route::get('/reports/{report}/edit', [ReportController::class, 'edit'])
    ->middleware(['session.auth', 'permission:sua_baocao'])
    ->name('reports.edit');

Route::put('/reports/{report}', [ReportController::class, 'update'])
    ->middleware(['session.auth', 'permission:sua_baocao'])
    ->name('reports.update');

Route::post('/reports/{report}', [ReportController::class, 'update'])
    ->middleware(['session.auth', 'permission:sua_baocao'])
    ->name('reports.update.legacy');

Route::delete('/reports/{report}', [ReportController::class, 'destroy'])
    ->middleware(['session.auth', 'permission:xoa_baocao'])
    ->name('reports.destroy');

Route::get('/chatbot', [ChatbotMonitorController::class, 'index'])
    ->middleware(['session.auth', 'permission:su_dung_chatbot'])
    ->name('chatbot.index');

Route::get('/chatbot/{session}', [ChatbotMonitorController::class, 'show'])
    ->middleware(['session.auth', 'permission:su_dung_chatbot'])
    ->whereNumber('session')
    ->name('chatbot.show');

Route::post('/chatbot/ask', [ChatbotMonitorController::class, 'ask'])
    ->middleware(['session.auth', 'permission:su_dung_chatbot'])
    ->name('chatbot.ask');

Route::post('/chatbot/confirm-draft', [ChatbotMonitorController::class, 'confirmDraft'])
    ->middleware(['session.auth', 'permission:su_dung_chatbot'])
    ->name('chatbot.confirm-draft');

Route::get('/chatbot/brief', [ChatbotMonitorController::class, 'brief'])
    ->middleware(['session.auth', 'permission:su_dung_chatbot'])
    ->name('chatbot.brief');

Route::post('/chatbot/clear-history', [ChatbotMonitorController::class, 'clearHistory'])
    ->middleware(['session.auth', 'permission:su_dung_chatbot'])
    ->name('chatbot.clear-history');

Route::get('/search', [SearchController::class, 'index'])
    ->middleware(['session.auth'])
    ->name('search.index');

Route::get('/system-health', [SystemHealthController::class, 'index'])
    ->middleware(['session.auth', 'permission:xem_taikhoan'])
    ->name('system-health.index');
Route::post('/system-health/run-checks', [SystemHealthController::class, 'runChecks'])
    ->middleware(['session.auth', 'permission:xem_taikhoan'])
    ->name('system-health.run-checks');

Route::get('/services', [ServiceConsoleController::class, 'index'])
    ->middleware(['session.auth', 'permission:xem_phanquyen'])
    ->name('services.index');

Route::get('/permission-matrix', [RolePermissionController::class, 'index'])
    ->middleware(['session.auth', 'permission:xem_phanquyen'])
    ->name('permission-matrix.index');

Route::get('/permission-matrix/accounts/{account}', [RolePermissionController::class, 'showAccount'])
    ->middleware(['session.auth', 'permission:xem_phanquyen'])
    ->name('permission-matrix.show-account');

Route::post('/permission-matrix/{role}', [RolePermissionController::class, 'update'])
    ->middleware(['session.auth', 'permission:sua_taikhoan'])
    ->name('permission-matrix.update');

Route::post('/permission-matrix/{role}/restore-defaults', [RolePermissionController::class, 'restoreDefaults'])
    ->middleware(['session.auth', 'permission:sua_taikhoan'])
    ->name('permission-matrix.restore-defaults');

Route::get('/services/{service}/{resource}', [ServiceConsoleController::class, 'show'])
    ->middleware(['session.auth', 'permission:xem_phanquyen'])
    ->name('services.show');

Route::get('/services/{service}/{resource}/create', [ServiceConsoleController::class, 'create'])
    ->middleware(['session.auth', 'permission:xem_phanquyen'])
    ->name('services.create');

Route::post('/services/{service}/{resource}', [ServiceConsoleController::class, 'store'])
    ->middleware(['session.auth', 'permission:xem_phanquyen'])
    ->name('services.store');

Route::get('/services/{service}/{resource}/{id}/edit', [ServiceConsoleController::class, 'edit'])
    ->middleware(['session.auth', 'permission:xem_phanquyen'])
    ->name('services.edit');

Route::get('/contracts/{contract}/renew', [ContractAdminController::class, 'renewForm'])
    ->middleware(['session.auth', 'permission:giahan_hopdong'])
    ->name('contracts.renew');
Route::get('/hopdong/{contract}/renew', [ContractAdminController::class, 'renewForm'])
    ->middleware(['session.auth', 'permission:giahan_hopdong'])
    ->name('hopdong.renew');

Route::post('/contracts/{contract}/renew', [ContractAdminController::class, 'renewStore'])
    ->middleware(['session.auth', 'permission:giahan_hopdong'])
    ->name('contracts.renew.store');
Route::post('/hopdong/{contract}/renew', [ContractAdminController::class, 'renewStore'])
    ->middleware(['session.auth', 'permission:giahan_hopdong'])
    ->name('hopdong.renew.store');

Route::post('/contracts/{contract}/terminate', [ContractAdminController::class, 'terminate'])
    ->middleware(['session.auth', 'permission:chamdut_hopdong'])
    ->name('contracts.terminate');
Route::post('/hopdong/{contract}/terminate', [ContractAdminController::class, 'terminate'])
    ->middleware(['session.auth', 'permission:chamdut_hopdong'])
    ->name('hopdong.terminate');

Route::get('/contracts/{contract}/terminate-legacy', [ContractAdminController::class, 'terminate'])
    ->middleware(['session.auth', 'permission:chamdut_hopdong'])
    ->name('contracts.terminate.legacy');
Route::get('/hopdong/{contract}/terminate-legacy', [ContractAdminController::class, 'terminate'])
    ->middleware(['session.auth', 'permission:chamdut_hopdong'])
    ->name('hopdong.terminate.legacy');

Route::get('/contracts/{contract}/salary-history', [ContractAdminController::class, 'salaryHistory'])
    ->middleware(['session.auth', 'permission:xem_lich_su_luong'])
    ->name('contracts.salary-history');
Route::get('/hopdong/{contract}/salary-history', [ContractAdminController::class, 'salaryHistory'])
    ->middleware(['session.auth', 'permission:xem_lich_su_luong'])
    ->name('hopdong.salary-history');

Route::get('/contracts/{contract}/delete-legacy', [ContractAdminController::class, 'destroyLegacy'])
    ->middleware(['session.auth', 'permission:xoa_hopdong'])
    ->name('contracts.admin.destroy.legacy');
Route::get('/hopdong/{contract}/delete-legacy', [ContractAdminController::class, 'destroyLegacy'])
    ->middleware(['session.auth', 'permission:xoa_hopdong'])
    ->name('hopdong.admin.destroy.legacy');

Route::get('/employee-profiles/review-requests', [EmployeeProfileAdminController::class, 'reviewRequests'])
    ->middleware(['session.auth', 'permission:xem_nhanvien'])
    ->name('employee-profiles.review-requests');
Route::get('/hosocanhan/review-requests', [EmployeeProfileAdminController::class, 'reviewRequests'])
    ->middleware(['session.auth', 'permission:xem_nhanvien'])
    ->name('hosocanhan.review-requests');

Route::post('/employee-profiles/review-requests/{requestId}', [EmployeeProfileAdminController::class, 'resolveRequest'])
    ->middleware(['session.auth', 'permission:sua_nhanvien'])
    ->name('employee-profiles.review-requests.resolve');
Route::post('/hosocanhan/review-requests/{requestId}', [EmployeeProfileAdminController::class, 'resolveRequest'])
    ->middleware(['session.auth', 'permission:sua_nhanvien'])
    ->name('hosocanhan.review-requests.resolve');

Route::get('/employee-profiles/employee-info', [EmployeeProfileAdminController::class, 'employeeInfo'])
    ->middleware(['session.auth', 'permission:xem_nhanvien'])
    ->name('employee-profiles.employee-info');
Route::get('/hosocanhan/employee-info', [EmployeeProfileAdminController::class, 'employeeInfo'])
    ->middleware(['session.auth', 'permission:xem_nhanvien'])
    ->name('hosocanhan.employee-info');

Route::get('/employee-profiles/{profile}/detail', [EmployeeProfileAdminController::class, 'show'])
    ->middleware(['session.auth', 'permission:xem_nhanvien'])
    ->name('employee-profiles.show');
Route::get('/hosocanhan/{profile}/detail', [EmployeeProfileAdminController::class, 'show'])
    ->middleware(['session.auth', 'permission:xem_nhanvien'])
    ->name('hosocanhan.show');
Route::put('/services/{service}/{resource}/{id}', [ServiceConsoleController::class, 'update'])
    ->middleware(['session.auth', 'permission:xem_phanquyen'])
    ->name('services.update');

Route::delete('/services/{service}/{resource}/{id}', [ServiceConsoleController::class, 'destroy'])
    ->middleware(['session.auth', 'permission:xem_phanquyen'])
    ->name('services.destroy');

Route::get('/admin/phanquyen', function () {
    return response()->json([
        'ok' => true,
        'message' => 'Ban co quyen xem_phanquyen trong Laravel middleware.',
    ]);
})->middleware(['session.auth', 'permission:xem_phanquyen']);

Route::post('/accounts/{account}/reset-temporary', [AccountAdminController::class, 'resetTemporaryPassword'])
    ->middleware(['session.auth', 'permission:sua_taikhoan'])
    ->name('accounts.reset-temporary');
Route::post('/taikhoan/{account}/reset-temporary', [AccountAdminController::class, 'resetTemporaryPassword'])
    ->middleware(['session.auth', 'permission:sua_taikhoan'])
    ->name('taikhoan.reset-temporary');

Route::get('/accounts/{account}/delete-legacy', [AccountAdminController::class, 'destroyLegacy'])
    ->middleware(['session.auth', 'permission:xoa_taikhoan'])
    ->name('accounts.admin.destroy.legacy');
Route::get('/taikhoan/{account}/delete-legacy', [AccountAdminController::class, 'destroyLegacy'])
    ->middleware(['session.auth', 'permission:xoa_taikhoan'])
    ->name('taikhoan.admin.destroy.legacy');

$registerResourceModuleRoutes = function (string $prefix, string $namePrefix, array $moduleConfig, string $moduleKey) {
    Route::prefix($prefix)->name($namePrefix . '.')->group(function () use ($moduleConfig, $moduleKey) {
        $isReadOnly = (bool) ($moduleConfig['read_only'] ?? false);
        $disableExport = (bool) ($moduleConfig['disable_export'] ?? false);

        Route::get('/', [ResourceModuleController::class, 'index'])
            ->name('index')
            ->middleware(['session.auth', 'permission:' . $moduleConfig['permission']['view']])
            ->defaults('module', $moduleKey);

        if (!$disableExport) {
            Route::get('/export-excel', [ResourceModuleController::class, 'exportExcel'])
                ->name('export-excel')
                ->middleware(['session.auth', 'permission:' . $moduleConfig['permission']['view']])
                ->defaults('module', $moduleKey);
        }

        if (!$isReadOnly) {
            Route::get('/create', [ResourceModuleController::class, 'create'])
                ->name('create')
                ->middleware(['session.auth', 'permission:' . $moduleConfig['permission']['create']])
                ->defaults('module', $moduleKey);

            Route::post('/', [ResourceModuleController::class, 'store'])
                ->name('store')
                ->middleware(['session.auth', 'permission:' . $moduleConfig['permission']['create']])
                ->defaults('module', $moduleKey);

            Route::get('/{record}/edit', [ResourceModuleController::class, 'edit'])
                ->name('edit')
                ->middleware(['session.auth', 'permission:' . $moduleConfig['permission']['update']])
                ->defaults('module', $moduleKey);

            Route::put('/{record}', [ResourceModuleController::class, 'update'])
                ->name('update')
                ->middleware(['session.auth', 'permission:' . $moduleConfig['permission']['update']])
                ->defaults('module', $moduleKey);

            Route::post('/{record}', [ResourceModuleController::class, 'update'])
                ->name('update.legacy')
                ->middleware(['session.auth', 'permission:' . $moduleConfig['permission']['update']])
                ->defaults('module', $moduleKey);

            Route::delete('/{record}', [ResourceModuleController::class, 'destroy'])
                ->name('destroy')
                ->middleware(['session.auth', 'permission:' . $moduleConfig['permission']['delete']])
                ->defaults('module', $moduleKey);

            Route::get('/{record}/delete-legacy', [ResourceModuleController::class, 'destroyLegacy'])
                ->name('destroy.legacy')
                ->middleware(['session.auth', 'permission:' . $moduleConfig['permission']['delete']])
                ->defaults('module', $moduleKey);
        }

        if (($moduleConfig['legacy_name'] ?? $moduleKey) === 'phancong') {
            Route::get('/history', [ResourceModuleController::class, 'assignmentHistory'])
                ->name('history')
                ->middleware(['session.auth', 'permission:xem_lichsu_phancong'])
                ->defaults('module', $moduleKey);
        }

        if (($moduleConfig['legacy_name'] ?? $moduleKey) === 'baohiem') {
            Route::get('/{record}/deactivate-legacy', [ResourceModuleController::class, 'deactivateInsurance'])
                ->name('deactivate.legacy')
                ->middleware(['session.auth', 'permission:dung_baohiem'])
                ->defaults('module', $moduleKey);
        }

        if (($moduleConfig['legacy_name'] ?? $moduleKey) === 'nghiphep') {
            Route::get('/{record}/approve-legacy', [ResourceModuleController::class, 'approveLeaveRequest'])
                ->name('approve.legacy')
                ->middleware(['session.auth', 'permission:duyet_nghiphep'])
                ->defaults('module', $moduleKey);

            Route::get('/{record}/reject-legacy', [ResourceModuleController::class, 'rejectLeaveRequest'])
                ->name('reject.legacy')
                ->middleware(['session.auth', 'permission:tuchoi_nghiphep'])
                ->defaults('module', $moduleKey);
        }
    });
};

foreach (config('laravel_resource_modules', []) as $moduleKey => $moduleConfig) {
    $registerResourceModuleRoutes($moduleKey, $moduleKey, $moduleConfig, $moduleKey);

    $legacyPrefix = (string) ($moduleConfig['legacy_prefix'] ?? '');
    $legacyName = (string) ($moduleConfig['legacy_name'] ?? $legacyPrefix);

    if ($legacyPrefix !== '' && ($legacyPrefix !== $moduleKey || $legacyName !== $moduleKey)) {
        $registerResourceModuleRoutes($legacyPrefix, $legacyName, $moduleConfig, $moduleKey);
    }
}

require __DIR__ . '/web_legacy.php';
