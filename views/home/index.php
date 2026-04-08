<?php include 'views/layout/header.php'; ?>

<?php include 'views/layout/sidebar.php'; ?>
<?php
$homeAccount = $_SESSION['taikhoan'] ?? [];
$homeRoleRaw = trim((string)($homeAccount['VaiTro'] ?? ($_SESSION['VaiTro'] ?? '')));
$homeRoleLower = strtolower($homeRoleRaw);
$homeRoleCompact = str_replace(' ', '', $homeRoleLower);
$hasEmployeeCode = trim((string)($homeAccount['MaNV'] ?? '')) !== '';
$isManagementRole = in_array($homeRoleCompact, ['admin', 'quanly', 'hr', 'ketoan'], true);
$showQuickProfileFab = $hasEmployeeCode && !$isManagementRole;
?>

<main class="main-content">

<div class="header-top">

<div>
<h1 data-i18n="home.dashboard_title">Dashboard</h1>
<p data-i18n="home.dashboard_subtitle">Quản lý tổng quan hệ thống nhân sự</p>
</div>

<div class="header-actions">
  <div class="search-box">
    <input type="text" id="search" placeholder="🔎 Tìm nhân viên..." data-i18n-placeholder="home.search_employee_placeholder"><div id="result"></div>
  </div>

  <div class="action-group">
    <div
      class="home-notification-menu"
      id="homeNotificationMenu"
      data-unread-leave="<?php echo (int)($unreadNghiPhep ?? 0); ?>"
      data-unread-contract="<?php echo (int)($unreadHopDong ?? 0); ?>"
      data-unread-candidate="<?php echo (int)($unreadUngVien ?? 0); ?>"
    >
      <button type="button" class="notification-icon" id="notificationToggle" title="Thông báo" data-i18n-title="home.notifications_title" aria-controls="notificationDropdown" aria-expanded="false">
        🔔
        <span class="badge"><?php echo (int)($unreadNghiPhep ?? 0) + (int)($unreadHopDong ?? 0) + (int)($unreadUngVien ?? 0); ?></span>
      </button>

      <div class="notification-dropdown" id="notificationDropdown" aria-hidden="true">
        <div class="notification-dropdown-header">
          <h4 data-i18n="home.notifications_new">Thông báo mới</h4>
          <button type="button" class="mark-all-read" id="markAllReadBtn" data-i18n="home.notifications_mark_all_read">Đánh dấu đã đọc tất cả</button>
        </div>
        <div class="notification-dropdown-list">
          <?php if ($tbNghiPhep > 0) { ?>
            <a href="index.php?controller=nghiphep&action=index" class="notification-mini-item warning notif-link" data-notif-key="leave">📆 <?php echo $tbNghiPhep; ?> đơn nghỉ phép chờ duyệt <small><?php echo htmlspecialchars($lastNghiPhepText, ENT_QUOTES, 'UTF-8'); ?></small></a>
          <?php } ?>

          <?php if ($tbHopDong > 0) { ?>
            <a href="index.php?controller=hopdong&action=index" class="notification-mini-item danger notif-link" data-notif-key="contract">📄 <?php echo $tbHopDong; ?> hợp đồng sắp hết hạn <small><?php echo htmlspecialchars($lastHopDongText, ENT_QUOTES, 'UTF-8'); ?></small></a>
          <?php } ?>

          <?php if ($tbUngVien > 0) { ?>
            <a href="index.php?controller=tuyendung&action=index" class="notification-mini-item success notif-link" data-notif-key="candidate">💼 <?php echo $tbUngVien; ?> ứng viên mới <small><?php echo htmlspecialchars($lastUngVienText, ENT_QUOTES, 'UTF-8'); ?></small></a>
          <?php } ?>

          <?php if ($tbNghiPhep == 0 && $tbHopDong == 0 && $tbUngVien == 0) { ?>
            <div class="notification-mini-item neutral" data-i18n="home.no_notifications">✅ Không có thông báo</div>
          <?php } ?>
        </div>
      </div>
    </div>

    
  </div>
</div>
</div>

<!-- STATS -->
<div class="stats">

<div class="stat-box">
  <div class="stat-top"><span class="stat-icon">👥</span><span class="stat-label" data-i18n="home.stats.staff">Nhân sự</span></div>
  <h2><?php echo $tongNhanVien ?></h2>
  <p data-i18n="home.stats.total_staff">Tổng nhân viên</p>
  <span class="stat-note success">▲ 4.8% so với tuần trước</span>
</div>

<div class="stat-box">
  <div class="stat-top"><span class="stat-icon">🏢</span><span class="stat-label" data-i18n="home.stats.departments">Phòng ban</span></div>
  <h2><?php echo $tongPhongBan ?></h2>
  <p data-i18n="home.stats.departments">Phòng ban</p>
  <span class="stat-note info">● 6 phòng đang mở</span>
</div>

<div class="stat-box">
  <div class="stat-top"><span class="stat-icon">📝</span><span class="stat-label" data-i18n="home.stats.leave_requests">Đơn phép</span></div>
  <h2><?php echo $donNghiChoDuyet ?></h2>
  <p data-i18n="home.stats.pending_leave">Đơn nghỉ chờ duyệt</p>
  <span class="stat-note warning">↔ 2 đơn mới</span>
</div>

<div class="stat-box">
  <div class="stat-top"><span class="stat-icon">💼</span><span class="stat-label" data-i18n="home.stats.candidates">Ứng viên</span></div>
  <h2><?php echo $tongUngVien ?></h2>
  <p data-i18n="home.stats.candidates">Ứng viên</p>
  <span class="stat-note success">▲ 7.3% so với tháng trước</span>
</div>

</div>

<!-- CHART -->
<div class="chart-container">

<div class="chart-box">
<h3 data-i18n="home.chart.staff_by_dept">📊 Nhân viên theo phòng ban</h3>
<canvas id="nhanVienChart"></canvas>
</div>

<div class="chart-box">
<h3 data-i18n="home.chart.staff_by_gender">👨‍💼 Nhân viên theo giới tính</h3>
<canvas id="gioiTinhChart"></canvas>
</div>

<div class="chart-box">
<h3 data-i18n="home.chart.avg_salary_by_dept">💰 Lương trung bình phòng ban</h3>
<canvas id="luongChart"></canvas>
</div>

</div>

<!-- THÔNG BÁO -->
<div class="notification-box">

<h2 data-i18n="home.notifications_title">🔔 Thông báo</h2>
<div class="notification-grid">
  <?php if($tbNghiPhep > 0){ ?>
  <div class="notification-item warning <?php echo ((int)$unreadNghiPhep > 0) ? 'unread' : 'read'; ?>" data-summary-key="leave">
    <div class="notify-main">📆 <?php echo $tbNghiPhep ?> đơn nghỉ phép chờ duyệt</div>
    <div class="notify-meta">
      <span class="notify-time"><?php echo htmlspecialchars($lastNghiPhepText, ENT_QUOTES, 'UTF-8'); ?></span>
      <span class="notify-state <?php echo ((int)$unreadNghiPhep > 0) ? 'unread' : 'read'; ?>"><?php echo ((int)$unreadNghiPhep > 0) ? 'Chưa đọc' : 'Đã đọc'; ?></span>
    </div>
  </div>
  <?php } ?>

  <?php if($tbHopDong > 0){ ?>
  <div class="notification-item danger <?php echo ((int)$unreadHopDong > 0) ? 'unread' : 'read'; ?>" data-summary-key="contract">
    <div class="notify-main">📄 <?php echo $tbHopDong ?> hợp đồng sắp hết hạn</div>
    <div class="notify-meta">
      <span class="notify-time"><?php echo htmlspecialchars($lastHopDongText, ENT_QUOTES, 'UTF-8'); ?></span>
      <span class="notify-state <?php echo ((int)$unreadHopDong > 0) ? 'unread' : 'read'; ?>"><?php echo ((int)$unreadHopDong > 0) ? 'Chưa đọc' : 'Đã đọc'; ?></span>
    </div>
  </div>
  <?php } ?>

  <?php if($tbUngVien > 0){ ?>
  <div class="notification-item success <?php echo ((int)$unreadUngVien > 0) ? 'unread' : 'read'; ?>" data-summary-key="candidate">
    <div class="notify-main">💼 <?php echo $tbUngVien ?> ứng viên mới</div>
    <div class="notify-meta">
      <span class="notify-time"><?php echo htmlspecialchars($lastUngVienText, ENT_QUOTES, 'UTF-8'); ?></span>
      <span class="notify-state <?php echo ((int)$unreadUngVien > 0) ? 'unread' : 'read'; ?>"><?php echo ((int)$unreadUngVien > 0) ? 'Chưa đọc' : 'Đã đọc'; ?></span>
    </div>
  </div>
  <?php } ?>

  <?php if($tbNghiPhep == 0 && $tbHopDong == 0 && $tbUngVien == 0){ ?>
  <div class="notification-item neutral" data-i18n="home.no_notifications">✅ Không có thông báo</div>
  <?php } ?>
</div>

</div>

<!-- MENU NHANH -->
<section class="dashboard">

<div class="card">
<div class="card-title"><span class="card-icon">👥</span><h3 data-i18n="home.dashboard.employee">Nhân viên</h3></div>
<p class="card-meta"><span data-i18n="home.dashboard.total_prefix">Tổng</span> <?php echo $tongNhanVien ?> <span data-i18n="home.dashboard.employee">nhân viên</span></p>
<a href="index.php?controller=nhanvien&action=index" data-i18n="home.view_details">Xem chi tiết</a>
</div>

<div class="card">
<div class="card-title"><span class="card-icon">🏢</span><h3 data-i18n="home.dashboard.department">Phòng ban</h3></div>
<p class="card-meta"><?php echo $tongPhongBan ?> <span data-i18n="home.dashboard.department">phòng ban</span></p>
<a href="index.php?controller=phongban&action=index" data-i18n="home.view_details">Xem chi tiết</a>
</div>

<div class="card">
<div class="card-title"><span class="card-icon">💰</span><h3 data-i18n="home.dashboard.salary">Lương</h3></div>
<p class="card-meta" data-i18n="home.dashboard.salary_meta">Kiểm tra bảng lương</p>
<a href="index.php?controller=luong&action=index" data-i18n="home.view_details">Xem chi tiết</a>
</div>

<div class="card">
<div class="card-title"><span class="card-icon">🕒</span><h3 data-i18n="home.dashboard.attendance">Chấm công</h3></div>
<p class="card-meta" data-i18n="home.dashboard.attendance_meta">Quản lý ngày công</p>
<a href="index.php?controller=chamcong&action=index" data-i18n="home.view_details">Xem chi tiết</a>
</div>

<div class="card">
<div class="card-title"><span class="card-icon">📄</span><h3 data-i18n="home.dashboard.contract">Hợp đồng</h3></div>
<p class="card-meta" data-i18n="home.dashboard.contract_meta">Theo dõi hợp đồng</p>
<a href="index.php?controller=hopdong&action=index" data-i18n="home.view_details">Xem chi tiết</a>
</div>

<div class="card">
<div class="card-title"><span class="card-icon">📊</span><h3 data-i18n="home.dashboard.report">Báo cáo</h3></div>
<p class="card-meta" data-i18n="home.dashboard.report_meta">Báo cáo hiệu suất</p>
<a href="index.php?controller=baocao&action=index" data-i18n="home.view_details">Xem chi tiết</a>
</div>

</section>

<?php if ($showQuickProfileFab) { ?>
<a
  href="index.php?controller=hosocanhan&action=nhapnhanh"
  class="quick-profile-fab"
  data-tooltip="Them ho so cua toi"
  aria-label="Them nhanh thong tin ho so"
  title="Them nhanh thong tin ho so"
>
  <span class="quick-profile-fab-core" aria-hidden="true">
    <img class="quick-profile-fab-avatar" src="public/anh/anh4.png" alt="Them ho so">
    <span class="quick-profile-fab-icon">+</span>
  </span>
  <span class="quick-profile-fab-text">Them nhanh ho so</span>
</a>
<?php } ?>

</main>

<?php include 'views/layout/footer.php'; ?>

<!-- SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const homeLang = document.documentElement.getAttribute('data-language') === 'en' ? 'en' : 'vi';
const homeText = {
  vi: {
    chartEmployee: 'Nhân viên',
    chartMale: 'Nam',
    chartFemale: 'Nữ',
    chartAvgSalary: 'Lương TB',
    unread: 'Chưa đọc',
    read: 'Đã đọc'
  },
  en: {
    chartEmployee: 'Employees',
    chartMale: 'Male',
    chartFemale: 'Female',
    chartAvgSalary: 'Avg Salary',
    unread: 'Unread',
    read: 'Read'
  }
};
const txt = homeText[homeLang];

// CHART NHÂN VIÊN
var labels=[];
var data=[];
<?php foreach($nhanVienPhongBan as $row){ ?>
labels.push(<?php echo json_encode($row['TenPB'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
data.push(<?php echo (int)$row['SoLuong']; ?>);
<?php } ?>

new Chart(document.getElementById('nhanVienChart'),{
type:'bar',
data:{labels:labels,datasets:[{label:txt.chartEmployee,data:data,backgroundColor:'#3498db'}]}
});

// CHART GIỚI TÍNH
new Chart(document.getElementById('gioiTinhChart'),{
type:'pie',
data:{labels:[txt.chartMale,txt.chartFemale],datasets:[{data:[<?php echo (int)$tongNam ?>,<?php echo (int)$tongNu ?>],backgroundColor:['#3498db','#e84393']}]}
});

// CHART LƯƠNG
var labelsLuong=[];
var dataLuong=[];
<?php foreach($luongPhongBan as $row){ ?>
labelsLuong.push(<?php echo json_encode($row['TenPB'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
dataLuong.push(<?php echo (float)$row['LuongTB']; ?>);
<?php } ?>

new Chart(document.getElementById('luongChart'),{
type:'bar',
data:{labels:labelsLuong,datasets:[{label:txt.chartAvgSalary,data:dataLuong,backgroundColor:'#2ecc71'}]}
});

// SEARCH AJAX
const searchInput = document.getElementById("search");
const searchResult = document.getElementById("result");
searchInput.addEventListener("keyup", function(){
  const keyword = this.value.trim();
  if(keyword.length === 0){
    searchResult.style.display = "none";
    searchResult.innerHTML = "";
    return;
  }

  fetch("search_ajax.php?keyword=" + encodeURIComponent(keyword))
    .then(res => res.text())
    .then(data => {
      searchResult.style.display = "block";
      searchResult.innerHTML = data;
    });
});

// USER MENU
function toggleMenu(){
  const panel = document.getElementById("userDropdown");
  const arrow = document.getElementById("arrow");
  const notificationPanel = document.getElementById('notificationDropdown');
  const notificationToggle = document.getElementById('notificationToggle');

  if (notificationPanel) {
    notificationPanel.classList.remove('show');
    notificationPanel.setAttribute('aria-hidden', 'true');
  }
  if (notificationToggle) {
    notificationToggle.setAttribute('aria-expanded', 'false');
  }

  const isOpen = panel.classList.toggle("show");
  panel.setAttribute('data-open', isOpen);
  arrow.style.transform = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
}

const notificationToggle = document.getElementById('notificationToggle');
const notificationDropdown = document.getElementById('notificationDropdown');
const notificationMenu = document.getElementById('homeNotificationMenu');
const markAllReadBtn = document.getElementById('markAllReadBtn');

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? (meta.getAttribute('content') || '') : '';
}

function markNotificationRead(type) {
  const token = getCsrfToken();
  const payload = new URLSearchParams();
  payload.append('type', type);
  payload.append('_csrf_token', token);

  return fetch('index.php?controller=home&action=markNotificationsRead', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      'X-CSRF-Token': token
    },
    body: payload.toString(),
    credentials: 'same-origin'
  });
}

function getNotificationState() {
  if (!notificationMenu) {
    return { leave: 0, contract: 0, candidate: 0 };
  }

  return {
    leave: parseInt(notificationMenu.getAttribute('data-unread-leave') || '0', 10),
    contract: parseInt(notificationMenu.getAttribute('data-unread-contract') || '0', 10),
    candidate: parseInt(notificationMenu.getAttribute('data-unread-candidate') || '0', 10)
  };
}

function setNotificationState(nextState) {
  if (!notificationMenu) return;
  notificationMenu.setAttribute('data-unread-leave', String(Math.max(0, nextState.leave || 0)));
  notificationMenu.setAttribute('data-unread-contract', String(Math.max(0, nextState.contract || 0)));
  notificationMenu.setAttribute('data-unread-candidate', String(Math.max(0, nextState.candidate || 0)));
}

function updateNotificationBadge() {
  if (!notificationToggle) return;

  const badge = notificationToggle.querySelector('.badge');
  if (!badge) return;

  const unread = getNotificationState();
  const unseenTotal = Math.max(0, unread.leave) + Math.max(0, unread.contract) + Math.max(0, unread.candidate);

  badge.textContent = String(unseenTotal);
  badge.style.display = unseenTotal > 0 ? 'inline-block' : 'none';
}

function syncSummaryReadState(unread) {
  var summaryItems = document.querySelectorAll('.notification-item[data-summary-key]');
  summaryItems.forEach(function (item) {
    var key = item.getAttribute('data-summary-key');
    if (!key) return;

    var hasUnread = (unread[key] || 0) > 0;
    item.classList.remove('read', 'unread');
    item.classList.add(hasUnread ? 'unread' : 'read');

    var state = item.querySelector('.notify-state');
    if (state) {
      state.classList.remove('read', 'unread');
      state.classList.add(hasUnread ? 'unread' : 'read');
      state.textContent = hasUnread ? txt.unread : txt.read;
    }
  });
}

if (notificationToggle && notificationDropdown) {
  updateNotificationBadge();
  syncSummaryReadState(getNotificationState());

  const notifLinks = notificationDropdown.querySelectorAll('.notif-link[data-notif-key]');
  notifLinks.forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();

      const key = link.getAttribute('data-notif-key');
      const targetUrl = link.getAttribute('href') || 'index.php?controller=home';
      if (!key) {
        window.location.href = targetUrl;
        return;
      }

      const unread = getNotificationState();
      if (Object.prototype.hasOwnProperty.call(unread, key)) {
        unread[key] = 0;
        setNotificationState(unread);
      }
      updateNotificationBadge();
      syncSummaryReadState(unread);

      markNotificationRead(key)
        .catch(function () {})
        .finally(function () {
          window.location.href = targetUrl;
        });
    });
  });

  if (markAllReadBtn) {
    markAllReadBtn.addEventListener('click', function () {
      var unread = { leave: 0, contract: 0, candidate: 0 };
      setNotificationState(unread);
      updateNotificationBadge();
      syncSummaryReadState(unread);
      markNotificationRead('all').catch(function () {});
    });
  }

  notificationToggle.addEventListener('click', function (e) {
    e.stopPropagation();

    const userPanel = document.getElementById('userDropdown');
    const userArrow = document.getElementById('arrow');
    if (userPanel) {
      userPanel.classList.remove('show');
      userPanel.removeAttribute('data-open');
    }
    if (userArrow) {
      userArrow.style.transform = 'rotate(0deg)';
    }

    const isOpen = notificationDropdown.classList.toggle('show');
    notificationDropdown.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    notificationToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

    if (isOpen) {
      updateNotificationBadge();
    }
  });
}

window.addEventListener('click', function(e){
  const userMenu = document.querySelector('.home-user-menu');
  const panel = document.getElementById('userDropdown');
  const arrow = document.getElementById("arrow");
  const notifyMenu = document.getElementById('homeNotificationMenu');
  const notifyPanel = document.getElementById('notificationDropdown');
  const notifyToggle = document.getElementById('notificationToggle');

  if(userMenu && !userMenu.contains(e.target)){
    panel.classList.remove('show');
    panel.removeAttribute('data-open');
    arrow.style.transform = 'rotate(0deg)';
  }

  if (notifyMenu && !notifyMenu.contains(e.target) && notifyPanel && notifyToggle) {
    notifyPanel.classList.remove('show');
    notifyPanel.setAttribute('aria-hidden', 'true');
    notifyToggle.setAttribute('aria-expanded', 'false');
  }
});

</script>
