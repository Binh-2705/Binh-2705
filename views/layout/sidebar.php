<?php
require_once 'core/AuthMiddleware.php';

$current_controller = $_GET['controller'] ?? 'home';
$current_action = $_GET['action'] ?? 'index';

if (!function_exists('isGroupOpen')) {
    function isGroupOpen($controllers, $current) {
        return in_array($current, $controllers) ? 'open' : '';
    }
}

if (!function_exists('isActive')) {
    function isActive($ctrl, $current) {
        return ($ctrl === $current) ? 'active' : '';
    }
}

// --- Nhân sự ---
$can_nhanvien   = AuthMiddleware::has('xem_nhanvien');
$can_hosocanhan = AuthMiddleware::has('xem_hoso');
$can_phancong   = AuthMiddleware::has('xem_phancong');
$can_hopdong    = AuthMiddleware::has('xem_hopdong');
$can_tuyendung  = AuthMiddleware::has('xem_dot_tuyen');
$can_daotao     = AuthMiddleware::has('xem_khoa_dao_tao');
$show_nhansu    = $can_nhanvien || $can_hosocanhan || $can_phancong || $can_hopdong || $can_tuyendung || $can_daotao;

// --- Công & Phúc lợi ---
$can_chamcong   = AuthMiddleware::has('xem_chamcong');
$can_luong      = AuthMiddleware::has('xem_luong');
$can_nghiphep   = AuthMiddleware::has('xem_nghiphep');
$can_baohiem    = AuthMiddleware::has('xem_baohiem');
$can_khenthuong = AuthMiddleware::has('xem_khenthuong');
$show_cong      = $can_chamcong || $can_luong || $can_nghiphep || $can_baohiem || $can_khenthuong;

// --- Hệ thống ---
$can_phongban   = AuthMiddleware::has('xem_phongban');
$can_chucvu     = AuthMiddleware::has('xem_chucvu');
$can_ngachluong = AuthMiddleware::has('xem_ngachluong');
$can_bacluong   = AuthMiddleware::has('xem_bacluong');
$can_taikhoan   = AuthMiddleware::has('xem_taikhoan');
$can_phanquyen  = AuthMiddleware::has('xem_taikhoan'); // phân quyền chỉ Admin/quản trị mới vào
$can_admin_only = AuthMiddleware::isAdminSession();
$show_hethong   = $can_phongban || $can_chucvu || $can_ngachluong || $can_bacluong || $can_taikhoan || $can_phanquyen;

// --- Báo cáo ---
$can_baocao = AuthMiddleware::has('xem_baocao');
?>

<nav class="sidebar">

<h2 data-i18n="brand.title">HRM SYSTEM</h2>

<ul class="menu-list">

<li>
    <a href="?controller=home" class="<?= ($current_controller === 'home' && $current_action !== 'settings') ? 'active' : '' ?>">
        🏠 <span data-i18n="menu.home">Trang chủ</span>
    </a>
</li>

<li>
    <a href="?controller=home&action=settings" class="<?= ($current_controller === 'home' && $current_action === 'settings') ? 'active' : '' ?>">
        ⚙ <span data-i18n="menu.settings">Cài đặt</span>
    </a>
</li>

<?php if ($show_nhansu): ?>
<li class="has-submenu <?= isGroupOpen(['nhanvien','hosocanhan','phancong','hopdong','tuyendung','daotao'],$current_controller) ?>">
    <a href="#" class="menu-toggle">👥 <span data-i18n="menu.hr">Nhân sự</span> <span class="arrow">▼</span></a>
    <ul class="submenu">
        <?php if ($can_nhanvien): ?><li><a href="?controller=nhanvien" data-i18n="menu.employee">Nhân viên</a></li><?php endif; ?>
        <?php if ($can_hosocanhan): ?><li><a href="?controller=hosocanhan" data-i18n="menu.profile">Hồ sơ</a></li><?php endif; ?>
        <?php if ($can_phancong): ?><li><a href="?controller=phancong" data-i18n="menu.assignment">Công tác</a></li><?php endif; ?>
        <?php if ($can_hopdong): ?><li><a href="?controller=hopdong" data-i18n="menu.contract">Hợp đồng</a></li><?php endif; ?>
        <?php if ($can_tuyendung): ?><li><a href="?controller=tuyendung" data-i18n="menu.recruitment">Tuyển dụng</a></li><?php endif; ?>
        <?php if ($can_daotao): ?><li><a href="?controller=daotao" data-i18n="menu.training">Đào tạo</a></li><?php endif; ?>
    </ul>
</li>
<?php endif; ?>

<?php if ($show_cong): ?>
<li class="has-submenu <?= isGroupOpen(['chamcong','luong','nghiphep','baohiem','khenthuong'],$current_controller) ?>">
    <a href="#" class="menu-toggle">💰 <span data-i18n="menu.benefits">Công & Phúc lợi</span> <span class="arrow">▼</span></a>
    <ul class="submenu">
        <?php if ($can_chamcong): ?><li><a href="?controller=chamcong" data-i18n="menu.attendance">Chấm công</a></li><?php endif; ?>
        <?php if ($can_luong): ?><li><a href="?controller=luong" data-i18n="menu.salary">Lương</a></li><?php endif; ?>
        <?php if ($can_nghiphep): ?><li><a href="?controller=nghiphep" data-i18n="menu.leave">Nghỉ phép</a></li><?php endif; ?>
        <?php if ($can_baohiem): ?><li><a href="?controller=baohiem" data-i18n="menu.insurance">Bảo hiểm</a></li><?php endif; ?>
        <?php if ($can_khenthuong): ?><li><a href="?controller=khenthuong" data-i18n="menu.reward">Khen thưởng</a></li><?php endif; ?>
    </ul>
</li>
<?php endif; ?>

<?php if ($show_hethong): ?>
<li class="has-submenu <?= isGroupOpen(['phongban','chucvu','ngachluong','bacluong','taikhoan','phanquyen','auditlog'],$current_controller) ?>">
    <a href="#" class="menu-toggle">⚙ <span data-i18n="menu.system">Hệ thống</span> <span class="arrow">▼</span></a>
    <ul class="submenu">
        <?php if ($can_phongban): ?><li><a href="?controller=phongban" data-i18n="menu.department">Phòng ban</a></li><?php endif; ?>
        <?php if ($can_chucvu): ?><li><a href="?controller=chucvu" data-i18n="menu.position">Chức vụ</a></li><?php endif; ?>
        <?php if ($can_ngachluong): ?><li><a href="?controller=ngachluong" data-i18n="menu.salary_grade">Ngạch lương</a></li><?php endif; ?>
        <?php if ($can_bacluong): ?><li><a href="?controller=bacluong" data-i18n="menu.salary_step">Bậc lương</a></li><?php endif; ?>
        <?php if ($can_taikhoan): ?><li><a href="?controller=taikhoan" data-i18n="menu.account">Tài khoản</a></li><?php endif; ?>
        <?php if ($can_phanquyen): ?><li><a href="?controller=phanquyen" data-i18n="menu.permission">Phân quyền</a></li><?php endif; ?>
        <?php if ($can_taikhoan): ?><li><a href="?controller=auditlog">Nhật ký hệ thống</a></li><?php endif; ?>
        <?php if ($can_admin_only): ?><li><a href="?controller=systemhealth">Sức khỏe hệ thống</a></li><?php endif; ?>
    </ul>
</li>
<?php endif; ?>

<?php if ($can_baocao): ?>
<li>
    <a href="?controller=baocao">📊 <span data-i18n="menu.report">Báo cáo</span></a>
</li>
<?php endif; ?>

</ul>

<div class="dark-mode-toggle-wrap">
    <button class="dark-mode-btn" id="darkModeToggle" title="Chuyển chế độ sáng/tối" data-i18n-title="theme.switch_title">
        <span class="icon-moon">🌙</span>
        <span class="icon-sun">☀️</span>
        <span class="toggle-label" data-i18n="theme.dark_mode">Chế độ tối</span>
    </button>
</div>

</nav>