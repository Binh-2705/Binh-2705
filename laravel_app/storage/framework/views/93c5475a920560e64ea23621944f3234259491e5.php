<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'Hệ thống quản lý nhân sự'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"></noscript>
    <script>
    (function () {
        try {
            var root = document.documentElement;

            function getCookie(name) {
                var parts = (document.cookie || '').split(';');
                for (var i = 0; i < parts.length; i++) {
                    var part = parts[i].trim();
                    if (part.indexOf(name + '=') === 0) {
                        return decodeURIComponent(part.substring(name.length + 1));
                    }
                }
                return '';
            }

            var theme = localStorage.getItem('hrm-theme') || 'light';
            root.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'light');

            var density = localStorage.getItem('hrm-density') || 'comfortable';
            root.setAttribute('data-density', density === 'compact' ? 'compact' : 'comfortable');

            var notifications = localStorage.getItem('hrm-notifications') || 'on';
            if (notifications === 'off') {
                root.classList.add('notifications-off');
            }

            var language = localStorage.getItem('hrm-language') || getCookie('hrm-language') || 'vi';
            root.setAttribute('data-language', language === 'en' ? 'en' : 'vi');
        } catch (e) {}
    })();
    </script>

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>?v=20260420-5">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/legacy-bridge.css')); ?>?v=20260410-1">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/sidebar.css')); ?>?v=20260410-1">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/dashboard.css')); ?>?v=20260410-1">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/modules.css')); ?>?v=20260413-1">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/chatbot-widget.css')); ?>?v=20260420-1">
</head>
<body class="app-body">
    <?php
        $account = (array) session('taikhoan', []);
        $sessionPerms = (array) session('quyen', []);
        // Refresh permissions live from DB/cache so newly granted rights show immediately
        $maTKLayout = (int) ($account['MaTK'] ?? 0);
        if ($maTKLayout > 0) {
            try {
                $permissions = app(\App\Services\PermissionService::class)->getPermissionsByAccountId($maTKLayout);
                // Sync back to session so middleware checks stay consistent
                if ($permissions !== $sessionPerms) {
                    request()->session()->put('quyen', $permissions);
                }
            } catch (\Throwable $e) {
                $permissions = $sessionPerms;
            }
        } else {
            $permissions = $sessionPerms;
        }
        $resourceModules = config('laravel_resource_modules', []);
        $sidebarUsername = trim((string) ($account['TenDangNhap'] ?? 'Người dùng'));
        $sidebarRole = trim((string) ($account['VaiTro'] ?? 'Nhân viên'));
        $sidebarEmployeeCode = trim((string) ($account['MaNV'] ?? ''));
        $avatarSeed = $sidebarUsername !== '' ? $sidebarUsername : 'U';
        $avatarInitial = strtoupper(substr($avatarSeed, 0, 1));
        $sidebarRoleLower = strtolower($sidebarRole);
        $canNhanVien = in_array('xem_nhanvien', $permissions, true);
        $canHoSo = in_array('xem_hoso', $permissions, true) || in_array('xem_nhanvien', $permissions, true);
        $canPhanCong = in_array('xem_phancong', $permissions, true);
        $canHopDong = in_array('xem_hopdong', $permissions, true);
        $canTuyenDung = in_array('xem_dot_tuyen', $permissions, true);
        $canDaoTao = in_array('xem_khoa_dao_tao', $permissions, true);
        $showNhanSu = $canNhanVien || $canHoSo || $canPhanCong || $canHopDong || $canTuyenDung || $canDaoTao;
        $canChamCong = in_array('xem_chamcong', $permissions, true);
        $canLuong = in_array('xem_luong', $permissions, true);
        $canNghiPhep = in_array('xem_nghiphep', $permissions, true);
        $canBaoHiem = in_array('xem_baohiem', $permissions, true);
        $canKhenThuong = in_array('xem_khenthuong', $permissions, true);
        $showCong = $canChamCong || $canLuong || $canNghiPhep || $canBaoHiem || $canKhenThuong;
        $canPhongBan = in_array('xem_phongban', $permissions, true);
        $canChucVu = in_array('xem_chucvu', $permissions, true);
        $canNgachLuong = in_array('xem_ngachluong', $permissions, true);
        $canBacLuong = in_array('xem_bacluong', $permissions, true);
        $canTaiKhoan = in_array('xem_taikhoan', $permissions, true);
        $canPhanQuyen = in_array('xem_phanquyen', $permissions, true);
        $showHeThong = $canPhongBan || $canChucVu || $canNgachLuong || $canBacLuong || $canTaiKhoan || $canPhanQuyen;
        $canBaoCao = in_array('xem_baocao', $permissions, true);
        $flashMessages = [];

        if (session('success')) {
            $flashMessages[] = ['type' => 'success', 'text' => session('success')];
        }
        if (session('error')) {
            $flashMessages[] = ['type' => 'error', 'text' => session('error')];
        }
        if (session('message')) {
            $flashMessages[] = ['type' => 'info', 'text' => session('message')];
        }
        if (request()->query('msg')) {
            $flashMessages[] = ['type' => 'info', 'text' => (string) request()->query('msg')];
        }

        $hrGroupOpen = request()->routeIs('employees.*', 'nhanvien.*') || request()->routeIs('employee-profiles.*', 'hosocanhan.*') || request()->routeIs('assignments.*', 'phancong.*') || request()->routeIs('contracts.*', 'hopdong.*') || request()->routeIs('recruitment.*', 'tuyendung.*') || request()->routeIs('training.*', 'daotao.*');
        $benefitGroupOpen = request()->routeIs('attendance.*', 'chamcong.*') || request()->routeIs('payroll.*', 'luong.*') || request()->routeIs('leave-requests.*', 'nghiphep.*') || request()->routeIs('insurances.*', 'baohiem.*') || request()->routeIs('reward-records.*', 'khenthuong.*');
        $systemGroupOpen = request()->routeIs('departments.*', 'phongban.*') || request()->routeIs('positions.*', 'chucvu.*') || request()->routeIs('salary-bands.*', 'ngachluong.*') || request()->routeIs('salary-grades.*', 'bacluong.*') || request()->routeIs('accounts.*', 'taikhoan.*') || request()->routeIs('permission-matrix.*', 'phanquyen.*') || request()->routeIs('services.*') || request()->routeIs('system-health.*', 'systemhealth.*') || request()->routeIs('chatbot.*') || request()->routeIs('audit-logs.*', 'auditlog.*');
    ?>


    <?php if(!empty($flashMessages)): ?>
        <div class="flash-stack">
            <?php $__currentLoopData = $flashMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flash): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flash-alert flash-<?php echo e($flash['type']); ?>">
                    <?php echo e($flash['text']); ?>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <aside class="sidebar" id="appSidebar">
            <div class="sidebar-brand">
                <span class="sidebar-brand-mark">HR</span>
                <div class="sidebar-brand-copy">
                    <span class="sidebar-brand-kicker">Workforce Console</span>
                    <h2 data-i18n="brand.title">Hệ thống nhân sự</h2>
                    <p>Quản lý hồ sơ, công lương và vận hành nội bộ trong một không gian làm việc thống nhất.</p>
                </div>
            </div>

            <ul class="menu-list">
                <li>
                    <a href="<?php echo e(route('dashboard')); ?>" class="<?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>"><span class="menu-icon">HM</span><span data-i18n="menu.home">Trang chủ</span></a>
                </li>

                <?php if($showNhanSu): ?>
                    <li class="has-submenu <?php echo e($hrGroupOpen ? 'open' : ''); ?>">
                        <a href="#" class="menu-toggle"><span class="menu-icon">NS</span><span data-i18n="menu.hr">Nhân sự</span> <span class="arrow">+</span></a>
                        <ul class="submenu">
                            <?php if($canNhanVien): ?><li><a href="<?php echo e(route('nhanvien.index')); ?>" class="<?php echo e(request()->routeIs('employees.*', 'nhanvien.*') ? 'active' : ''); ?>">Nhân viên</a></li><?php endif; ?>
                            <?php if($canHoSo): ?><li><a href="<?php echo e(route('hosocanhan.index')); ?>" class="<?php echo e(request()->routeIs('employee-profiles.*', 'hosocanhan.*') ? 'active' : ''); ?>">Hồ sơ</a></li><?php endif; ?>
                            <?php if($canHoSo && in_array($sidebarRoleLower, ['nhanvien', 'admin', 'quanly', 'hr', 'ketoan'], true)): ?><li><a href="<?php echo e(route('hosocanhan.create')); ?>">Nhập nhanh hồ sơ</a></li><?php endif; ?>
                            <?php if($canHoSo && in_array($sidebarRoleLower, ['admin', 'quanly'], true)): ?><li><a href="<?php echo e(route('hosocanhan.review-requests')); ?>">Duyệt yêu cầu sửa</a></li><?php endif; ?>
                            <?php if($canPhanCong): ?><li><a href="<?php echo e(route('phancong.index')); ?>">Công tác</a></li><?php endif; ?>
                            <?php if($canHopDong): ?><li><a href="<?php echo e(route('hopdong.index')); ?>">Hợp đồng</a></li><?php endif; ?>
                            <?php if($canTuyenDung): ?><li><a href="<?php echo e(route('tuyendung.index')); ?>">Tuyển dụng</a></li><?php endif; ?>
                            <?php if($canDaoTao): ?><li><a href="<?php echo e(route('daotao.index')); ?>">Đào tạo</a></li><?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if($showCong): ?>
                    <li class="has-submenu <?php echo e($benefitGroupOpen ? 'open' : ''); ?>">
                        <a href="#" class="menu-toggle"><span class="menu-icon">PL</span><span data-i18n="menu.benefits">Công và phúc lợi</span> <span class="arrow">+</span></a>
                        <ul class="submenu">
                            <?php if($canChamCong): ?><li><a href="<?php echo e(route('chamcong.index')); ?>">Chấm công</a></li><?php endif; ?>
                            <?php if($canLuong): ?><li><a href="<?php echo e(route('luong.index')); ?>">Lương</a></li><?php endif; ?>
                            <?php if(Route::has('nghiphep.index') && $canNghiPhep): ?><li><a href="<?php echo e(route('nghiphep.index')); ?>">Nghỉ phép</a></li><?php endif; ?>
                            <?php if(Route::has('baohiem.index') && $canBaoHiem): ?><li><a href="<?php echo e(route('baohiem.index')); ?>">Bảo hiểm</a></li><?php endif; ?>
                            <?php if(Route::has('khenthuong.index') && $canKhenThuong): ?><li><a href="<?php echo e(route('khenthuong.index')); ?>">Khen thưởng</a></li><?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if($showHeThong): ?>
                    <li class="has-submenu <?php echo e($systemGroupOpen ? 'open' : ''); ?>">
                        <a href="#" class="menu-toggle"><span class="menu-icon">HT</span><span data-i18n="menu.system">Hệ thống</span> <span class="arrow">+</span></a>
                        <ul class="submenu">
                            <?php if($canPhongBan): ?><li><a href="<?php echo e(route('phongban.index')); ?>">Phòng ban</a></li><?php endif; ?>
                            <?php if(Route::has('chucvu.index') && $canChucVu): ?><li><a href="<?php echo e(route('chucvu.index')); ?>">Chức vụ</a></li><?php endif; ?>
                            <?php if(Route::has('ngachluong.index') && $canNgachLuong): ?><li><a href="<?php echo e(route('ngachluong.index')); ?>">Ngạch lương</a></li><?php endif; ?>
                            <?php if(Route::has('bacluong.index') && $canBacLuong): ?><li><a href="<?php echo e(route('bacluong.index')); ?>">Bậc lương</a></li><?php endif; ?>
                            <?php if(Route::has('taikhoan.index') && $canTaiKhoan): ?><li><a href="<?php echo e(route('taikhoan.index')); ?>">Tài khoản</a></li><?php endif; ?>
                            <?php if($canPhanQuyen): ?><li><a href="<?php echo e(route('phanquyen.index')); ?>">Phân quyền</a></li><?php endif; ?>
                            <?php if(Route::has('auditlog.index') && $canTaiKhoan): ?><li><a href="<?php echo e(route('auditlog.index')); ?>">Nhật ký hệ thống</a></li><?php endif; ?>
                            <?php if(in_array('su_dung_chatbot', $permissions, true)): ?><li><a href="<?php echo e(route('chatbot.index')); ?>" class="<?php echo e(request()->routeIs('chatbot.*') ? 'active' : ''); ?>">Nhật ký Chatbot</a></li><?php endif; ?>
                            <?php if($canTaiKhoan): ?><li><a href="<?php echo e(route('systemhealth.index')); ?>">Sức khỏe hệ thống</a></li><?php endif; ?>
                            <?php if($canPhanQuyen): ?><li><a href="<?php echo e(route('services.index')); ?>">Bảng dịch vụ</a></li><?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if($canBaoCao): ?>
                    <li>
                        <a href="<?php echo e(route('baocao.index')); ?>" class="<?php echo e(request()->routeIs('reports.*', 'baocao.*') ? 'active' : ''); ?>"><span class="menu-icon">BC</span><span data-i18n="menu.report">Báo cáo</span></a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="<?php echo e(route('search.index')); ?>" class="<?php echo e(request()->routeIs('search.*') ? 'active' : ''); ?>"><span class="menu-icon">TC</span><span>Tra cứu</span></a>
                </li>

                <li>
                    <a href="<?php echo e(route('settings.show')); ?>" class="<?php echo e(request()->routeIs('settings.*') ? 'active' : ''); ?>"><span class="menu-icon">CD</span><span data-i18n="menu.settings">Cài đặt</span></a>
                </li>
            </ul>

            <div class="sidebar-account" id="sidebarAccountWidget">
                <button type="button" class="sidebar-account-trigger" id="sidebarAccountTrigger" aria-expanded="false">
                    <span class="sidebar-avatar"><?php echo e($avatarInitial); ?></span>
                    <span class="sidebar-account-text">
                        <strong><?php echo e($sidebarUsername); ?></strong>
                        <small>
                            <span class="sidebar-status-dot"></span>
                            <span>Đang hoạt động</span>
                        </small>
                    </span>
                    <span class="sidebar-account-caret">▾</span>
                </button>

                <div class="sidebar-account-panel" id="sidebarAccountPanel">
                    <div class="sidebar-account-header">
                        <span class="sidebar-avatar sidebar-avatar-large"><?php echo e($avatarInitial); ?></span>
                        <div class="sidebar-account-identity">
                            <div class="sidebar-account-name"><?php echo e($sidebarUsername); ?></div>
                            <div class="sidebar-account-role"><?php echo e($sidebarRole); ?></div>
                        </div>
                    </div>

                    <div class="sidebar-account-meta">
                        <span class="sidebar-account-meta-label">Mã nhân sự</span>
                        <strong><?php echo e($sidebarEmployeeCode !== '' ? $sidebarEmployeeCode : 'Chưa gán'); ?></strong>
                    </div>

                    <div class="sidebar-account-actions">
                        <a href="<?php echo e(route('settings.show')); ?>">
                            <span class="sidebar-action-icon">⚙</span>
                            <span>Cài đặt tài khoản</span>
                        </a>
                        <form method="post" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn delete button-row button-full-row">
                                <span class="sidebar-action-icon">↪</span>
                                <span>Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <button type="button" class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></button>

        <main class="main-content">
            <div class="content-topbar">
                <button type="button" class="mobile-sidebar-toggle" id="mobileSidebarToggle" aria-controls="appSidebar" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="content-topbar-meta">
                    <span class="content-kicker">Không gian làm việc</span>
                    <div class="content-meta-row">
                        <span class="content-pill"><?php echo e($sidebarRole); ?></span>
                        <?php if($sidebarEmployeeCode !== ''): ?>
                            <span class="content-pill soft"><?php echo e($sidebarEmployeeCode); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="page-shell-header">
                <div class="page-shell-copy">
                    <span class="page-shell-kicker">HR Workspace</span>
                    <h1><?php echo e($title ?? 'Hệ thống quản lý nhân sự'); ?></h1>
                    <?php if(isset($subtitle)): ?>
                        <p><?php echo e($subtitle); ?></p>
                    <?php else: ?>
                        <p>Theo dõi nhân sự, tác vụ vận hành và dữ liệu nội bộ trong một giao diện rõ ràng hơn.</p>
                    <?php endif; ?>
                </div>
                <div class="page-shell-actions">
                    <a class="btn btn-secondary" href="<?php echo e(route('settings.show')); ?>">Cài đặt tài khoản</a>
                    <form method="post" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="btn btn-danger" type="submit">Đăng xuất</button>
                    </form>
                </div>
            </div>

            <div class="page-stage">
                <?php if($errors->any() && !session('error')): ?>
                    <div class="flash-alert flash-error flash-inline"><?php echo e($errors->first()); ?></div>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>
    </div>

    <script src="<?php echo e(asset('assets/js/sidebar.js')); ?>?v=20260420-5"></script>

    
    <?php $hasChat = in_array('su_dung_chatbot', (array)session('quyen', []), true); ?>
    <?php if($hasChat): ?>
    <div id="ai-chat-panel" role="dialog" aria-modal="true" aria-label="Trợ lý AI">
        <div class="ai-chat-header">
            <div class="ai-chat-header-avatar">🤖</div>
            <div class="ai-chat-header-text">
                <div class="ai-chat-header-title">Trợ lý AI Nhân sự</div>
                <div class="ai-chat-header-status">
                    <span class="ai-chat-status-dot"></span>
                    <span id="ai-chat-status-text">Sẵn sàng</span>
                </div>
            </div>
            <button type="button" class="ai-chat-header-btn" id="ai-chart-toggle" title="Xem biểu đồ">📊</button>
            <button type="button" class="ai-chat-header-btn" id="ai-chat-clear" title="Xoá lịch sử">🗑</button>
            <button type="button" class="ai-chat-header-btn" id="ai-chat-close" title="Đóng">✕</button>
        </div>
        
        <div id="ai-chart-panel" style="display:none; padding:12px 14px; border-bottom:1px solid var(--border-default,#e5e7eb); background:var(--surface-alt,#f9fafb);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <strong style="font-size:13px;">📊 Biểu đồ nhanh</strong>
                <select id="ai-chart-select" style="font-size:12px; padding:3px 8px; border-radius:6px; border:1px solid #d1d5db; background:#fff; cursor:pointer;">
                    <option value="department">Nhân viên / Phòng ban</option>
                    <option value="leave">Trạng thái nghỉ phép</option>
                    <option value="attendance">Chấm công 7 ngày</option>
                    <option value="recruitment">Tuyển dụng</option>
                    <option value="payroll">Lương tháng này</option>
                </select>
            </div>
            <div id="ai-chart-loading" style="text-align:center; padding:20px; color:#9ca3af; font-size:12px;">Đang tải…</div>
            <canvas id="ai-chart-canvas" width="320" height="180" style="display:none; max-height:180px;"></canvas>
        </div>
        <div class="ai-chat-messages" id="ai-chat-messages">
            <div class="ai-welcome" id="ai-welcome-state">
                <div class="ai-welcome-icon">🤖</div>
                <div class="ai-welcome-title">Xin chào! Tôi là Trợ lý AI</div>
                <div class="ai-welcome-sub">Hỏi tôi về nhân viên, nghỉ phép, hợp đồng, lương, chấm công hoặc bất cứ điều gì trong hệ thống.</div>
            </div>
        </div>
        <div class="ai-suggestions" id="ai-suggestions"></div>
        <div id="ai-draft-zone"></div>
        <div class="ai-chat-input-wrap">
            <textarea id="ai-chat-input" placeholder="Nhập câu hỏi…" rows="1" maxlength="900" autocomplete="off"></textarea>
            <button type="button" id="ai-chat-send" title="Gửi">➤</button>
        </div>
    </div>

    <button type="button" id="ai-chat-fab" aria-label="Mở trợ lý AI">
        <span id="ai-chat-fab-icon-open">🤖</span>
        <span id="ai-chat-fab-icon-close">✕</span>
        <span id="ai-chat-badge"></span>
    </button>

    <script>
    (function () {
        var panel      = document.getElementById('ai-chat-panel');
        var fab        = document.getElementById('ai-chat-fab');
        var messages   = document.getElementById('ai-chat-messages');
        var input      = document.getElementById('ai-chat-input');
        var sendBtn    = document.getElementById('ai-chat-send');
        var suggWrap   = document.getElementById('ai-suggestions');
        var draftZone  = document.getElementById('ai-draft-zone');
        var statusText = document.getElementById('ai-chat-status-text');
        var badge      = document.getElementById('ai-chat-badge');
        var welcome    = document.getElementById('ai-welcome-state');

        var isOpen    = false;
        var isBusy    = false;
        var unread    = 0;
        var askUrl    = '<?php echo e(route("chatbot.ask")); ?>';
        var confirmUrl= '<?php echo e(route("chatbot.confirm-draft")); ?>';
        var chartsUrl = '<?php echo e(route("dashboard.charts")); ?>';
        var csrf      = (document.querySelector('meta[name="csrf-token"]') || {}).getAttribute ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

        // ---- Chart panel ----
        var chartPanel    = document.getElementById('ai-chart-panel');
        var chartSelect   = document.getElementById('ai-chart-select');
        var chartLoading  = document.getElementById('ai-chart-loading');
        var chartCanvas   = document.getElementById('ai-chart-canvas');
        var chartToggleBtn= document.getElementById('ai-chart-toggle');
        var chartInstance = null;
        var chartDataCache= null;

        var PALETTE = ['#4f46e5','#7c3aed','#2563eb','#0891b2','#059669','#d97706','#dc2626','#db2777'];

        function fetchChartsData(cb) {
            if (chartDataCache) { cb(chartDataCache); return; }
            fetch(chartsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r){ return r.json(); })
                .then(function(d){ chartDataCache = d.charts || {}; cb(chartDataCache); })
                .catch(function(){ cb(null); });
        }

        function renderMiniChart(key) {
            if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
            chartCanvas.style.display = 'none';
            chartLoading.style.display = 'block';
            chartLoading.textContent = 'Đang tải…';

            fetchChartsData(function(data) {
                if (!data) { chartLoading.textContent = 'Không thể tải dữ liệu.'; return; }
                var c = data[key];
                if (!c || !c.labels || !c.labels.length) { chartLoading.textContent = 'Không có dữ liệu.'; return; }

                chartLoading.style.display = 'none';
                chartCanvas.style.display = 'block';

                var type = (key === 'leave' || key === 'recruitment') ? 'doughnut' : (key === 'attendance' ? 'line' : 'bar');
                var datasets = [{
                    data: c.values,
                    backgroundColor: type === 'line' ? 'rgba(79,70,229,.15)' : PALETTE.slice(0, c.values.length),
                    borderColor: type === 'line' ? '#4f46e5' : undefined,
                    borderWidth: type === 'line' ? 2 : 0,
                    borderRadius: type === 'bar' ? 5 : 0,
                    fill: type === 'line',
                    tension: type === 'line' ? 0.4 : 0,
                    pointRadius: type === 'line' ? 3 : 0,
                    pointBackgroundColor: type === 'line' ? '#4f46e5' : undefined,
                    hoverOffset: type === 'doughnut' ? 6 : 0,
                }];

                chartInstance = new Chart(chartCanvas, {
                    type: type,
                    data: { labels: c.labels, datasets: datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: type === 'doughnut', position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                        },
                        scales: type !== 'doughnut' ? { y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } } }, x: { ticks: { font: { size: 10 } } } } : {}
                    }
                });
            });
        }

        var chartOpen = false;
        var chartJsUrl = 'https://cdn.jsdelivr.net/npm/chart.js';
        function ensureChartJs(cb) {
            if (window.Chart) { cb(); return; }
            var s = document.createElement('script');
            s.src = chartJsUrl;
            s.onload = function() { cb(); };
            s.onerror = function() { chartLoading.textContent = 'Không tải được Chart.js.'; };
            document.head.appendChild(s);
        }

        chartToggleBtn.addEventListener('click', function() {
            chartOpen = !chartOpen;
            chartPanel.style.display = chartOpen ? 'block' : 'none';
            chartToggleBtn.style.background = chartOpen ? 'rgba(255,255,255,0.35)' : '';
            if (chartOpen) {
                chartLoading.textContent = 'Đang tải…';
                chartLoading.style.display = 'block';
                chartCanvas.style.display = 'none';
                ensureChartJs(function() { renderMiniChart(chartSelect.value); });
            }
        });

        chartSelect.addEventListener('change', function() {
            if (chartOpen) renderMiniChart(chartSelect.value);
        });

        var DEFAULT_SUGGESTIONS = [
            'Tổng số nhân viên là bao nhiêu?',
            'Nghỉ phép đang chờ duyệt?',
            'Hợp đồng sắp hết hạn',
            'Tóm tắt lương tháng này',
            'Chấm công tháng này',
        ];

        function openPanel() {
            isOpen = true;
            panel.classList.add('is-open');
            fab.classList.add('is-open');
            unread = 0;
            badge.classList.remove('visible');
            badge.textContent = '';
            input.focus();
            scrollBottom();
            if (!suggWrap.children.length) renderSuggestions(DEFAULT_SUGGESTIONS);
        }

        function closePanel() {
            isOpen = false;
            panel.classList.remove('is-open');
            fab.classList.remove('is-open');
        }

        fab.addEventListener('click', function () { isOpen ? closePanel() : openPanel(); });
        document.getElementById('ai-chat-close').addEventListener('click', closePanel);
        document.getElementById('ai-chat-clear').addEventListener('click', function () {
            messages.innerHTML = '';
            messages.appendChild(welcome);
            suggWrap.innerHTML = '';
            draftZone.innerHTML = '';
            renderSuggestions(DEFAULT_SUGGESTIONS);
        });

        function scrollBottom() {
            messages.scrollTop = messages.scrollHeight;
        }

        function appendMessage(role, text, source) {
            if (welcome && welcome.parentNode) welcome.parentNode.removeChild(welcome);

            var wrap = document.createElement('div');
            wrap.className = 'ai-msg ' + (role === 'user' ? 'user' : 'bot');

            var avatar = document.createElement('div');
            avatar.className = 'ai-msg-avatar';
            avatar.textContent = role === 'user' ? '👤' : '🤖';

            var inner = document.createElement('div');

            var bubble = document.createElement('div');
            bubble.className = 'ai-msg-bubble';
            bubble.textContent = text;

            inner.appendChild(bubble);

            if (source && role !== 'user') {
                var src = document.createElement('div');
                src.className = 'ai-msg-source';
                src.textContent = source === 'openai' ? '✦ GPT' : source === 'rule_based' ? '⚙ Rule-based' : source;
                inner.appendChild(src);
            }

            wrap.appendChild(avatar);
            wrap.appendChild(inner);
            messages.appendChild(wrap);
            scrollBottom();

            if (!isOpen) {
                unread++;
                badge.textContent = unread > 9 ? '9+' : String(unread);
                badge.classList.add('visible');
            }
        }

        function showTyping() {
            var t = document.createElement('div');
            t.className = 'ai-msg bot';
            t.id = 'ai-typing-indicator';
            var avatar = document.createElement('div');
            avatar.className = 'ai-msg-avatar';
            avatar.textContent = '🤖';
            var dots = document.createElement('div');
            dots.className = 'ai-typing';
            dots.innerHTML = '<span></span><span></span><span></span>';
            t.appendChild(avatar);
            t.appendChild(dots);
            messages.appendChild(t);
            scrollBottom();
        }

        function hideTyping() {
            var t = document.getElementById('ai-typing-indicator');
            if (t) t.parentNode.removeChild(t);
        }

        function renderSuggestions(list) {
            suggWrap.innerHTML = '';
            if (!list || !list.length) return;
            list.slice(0, 5).forEach(function (s) {
                var chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'ai-suggestion-chip';
                chip.textContent = s;
                chip.addEventListener('click', function () {
                    sendMessage(s);
                });
                suggWrap.appendChild(chip);
            });
        }

        function renderDraft(draft) {
            draftZone.innerHTML = '';
            if (!draft) return;
            var banner = document.createElement('div');
            banner.className = 'ai-draft-banner';
            banner.innerHTML = '<strong>⚡ ' + escHtml(draft.title || 'Xác nhận hành động') + '</strong>' +
                '<div>' + escHtml(draft.summary || '') + '</div>';

            var actions = document.createElement('div');
            actions.className = 'ai-draft-banner-actions';

            var confirmBtn = document.createElement('button');
            confirmBtn.type = 'button';
            confirmBtn.className = 'ai-draft-confirm';
            confirmBtn.textContent = draft.confirm_label || 'Xác nhận thực thi';
            confirmBtn.addEventListener('click', function () {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Đang thực thi…';
                executeDraft(draft.token, '');
            });

            var cancelBtn = document.createElement('button');
            cancelBtn.type = 'button';
            cancelBtn.className = 'ai-draft-cancel';
            cancelBtn.textContent = 'Huỷ';
            cancelBtn.addEventListener('click', function () { draftZone.innerHTML = ''; });

            actions.appendChild(confirmBtn);
            actions.appendChild(cancelBtn);
            banner.appendChild(actions);
            draftZone.appendChild(banner);
        }

        function executeDraft(token, reason) {
            var fd = new FormData();
            fd.append('_token', csrf);
            fd.append('action_token', token);
            if (reason) fd.append('confirm_reason', reason);

            fetch(confirmUrl, { method: 'POST', headers: { 'X-CSRF-Token': csrf }, body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    draftZone.innerHTML = '';
                    appendMessage('bot', d.ok ? '✅ ' + (d.message || 'Thực thi thành công!') : '❌ ' + (d.message || 'Thực thi thất bại.'), 'system');
                })
                .catch(function () {
                    draftZone.innerHTML = '';
                    appendMessage('bot', '❌ Không thể thực thi. Vui lòng thử lại.', 'system');
                });
        }

        function escHtml(str) {
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function sendMessage(text) {
            text = text.trim();
            if (!text || isBusy) return;

            isBusy = true;
            sendBtn.disabled = true;
            input.value = '';
            autoResize();
            statusText.textContent = 'Đang xử lý…';
            suggWrap.innerHTML = '';
            draftZone.innerHTML = '';

            appendMessage('user', text);
            showTyping();

            var fd = new FormData();
            fd.append('_token', csrf);
            fd.append('message', text);

            fetch(askUrl, { method: 'POST', headers: { 'X-CSRF-Token': csrf }, body: fd })
                .then(function (r) {
                    if (r.status === 401) { window.location.reload(); return null; }
                    return r.json();
                })
                .then(function (d) {
                    hideTyping();
                    if (!d) return;
                    if (!d.ok) {
                        appendMessage('bot', '⚠️ ' + (d.message || 'Lỗi không xác định.'), 'error');
                    } else {
                        appendMessage('bot', d.reply || '…', d.source || '');
                        renderSuggestions(d.suggestions || DEFAULT_SUGGESTIONS);
                        if (d.action_draft) renderDraft(d.action_draft);
                    }
                    statusText.textContent = 'Sẵn sàng';
                })
                .catch(function () {
                    hideTyping();
                    appendMessage('bot', '⚠️ Không thể kết nối tới bot service. Đảm bảo Python service đang chạy.', 'error');
                    statusText.textContent = 'Lỗi kết nối';
                })
                .finally(function () {
                    isBusy = false;
                    sendBtn.disabled = false;
                    input.focus();
                });
        }

        sendBtn.addEventListener('click', function () { sendMessage(input.value); });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(input.value);
            }
        });

        function autoResize() {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';
        }
        input.addEventListener('input', autoResize);

        renderSuggestions(DEFAULT_SUGGESTIONS);
    })();
    </script>
    <?php endif; ?>
    

    <script>
    (function () {
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (!tokenMeta) return;

        var token = tokenMeta.getAttribute('content') || '';
        if (!token) return;

        var postForms = document.querySelectorAll('form[method="post"], form[method="POST"]');
        postForms.forEach(function (form) {
            if (form.querySelector('input[name="_csrf_token"]')) return;

            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = '_csrf_token';
            hidden.value = token;
            form.appendChild(hidden);
        });

        if (window.jQuery && typeof window.jQuery.ajaxSetup === 'function') {
            window.jQuery.ajaxSetup({
                headers: {
                    'X-CSRF-Token': token
                }
            });
        }
    })();
    </script>

    <?php echo $__env->yieldPushContent('page_scripts'); ?>
</body>
</html><?php /**PATH C:\xampp\htdocs\du_an2\laravel_app\resources\views/layouts/app.blade.php ENDPATH**/ ?>