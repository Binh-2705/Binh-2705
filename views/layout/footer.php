</div> <!-- container -->

<?php
$currentController = $_GET['controller'] ?? 'home';
$showFloatingChatbot = !empty($_SESSION['taikhoan']) && 
                      $currentController !== 'chatbot' && 
                      in_array('su_dung_chatbot', (array)($_SESSION['quyen'] ?? []), true);
?>

<?php if ($showFloatingChatbot) { ?>
<div class="chatbot-float" id="chatbotFloat">
	<button type="button" class="chatbot-launcher" id="chatbotLauncher" aria-expanded="false" aria-controls="chatbotWidgetPanel">
		<span class="chatbot-launcher-art" aria-hidden="true">
			<img class="chatbot-launcher-image" src="public/anh/anh3.gif" alt="AI Chatbot Avatar" onerror="this.style.display='none'; this.parentElement.classList.add('no-image');">
			<span class="chatbot-launcher-bubble">Hi!</span>
		</span>
		<span class="chatbot-launcher-text">
			<strong>AI Chatbot</strong>
			<small>Hỏi nhanh mọi lúc</small>
		</span>
	</button>

	<section class="chatbot-widget-panel" id="chatbotWidgetPanel" aria-hidden="true">
		<div class="chatbot-widget-header">
			<div>
				<strong>AI Chatbot</strong>
				<p>Tra cứu và gợi ý nghiệp vụ ngay trên trang này.</p>
			</div>
			<button type="button" class="chatbot-widget-close" id="chatbotWidgetClose" aria-label="Đóng chatbot">×</button>
		</div>

		<section class="chatbot-shell chatbot-shell-compact" data-chatbot-shell data-endpoint="index.php?controller=chatbot&action=ask" data-confirm-endpoint="index.php?controller=chatbot&action=confirmDraft">
			<div class="chatbot-messages" aria-live="polite">
				<article class="chatbot-msg bot">
					<div class="chatbot-bubble">
						Xin chào. Tôi đang sẵn sàng hỗ trợ bạn tra cứu nhanh dữ liệu nhân sự.
					</div>
				</article>
			</div>

			<div class="chatbot-quick-actions">
				<button type="button" class="chatbot-chip" data-prompt="Tổng số nhân viên hiện tại là bao nhiêu?">Tổng nhân viên</button>
				<button type="button" class="chatbot-chip" data-prompt="Thống kê nghỉ phép">Thống kê nghỉ phép</button>
				<button type="button" class="chatbot-chip" data-prompt="Hợp đồng sắp hết hạn">Hợp đồng sắp hết hạn</button>
			</div>

			<form class="chatbot-form" method="post" action="index.php?controller=chatbot&action=ask">
				<input type="hidden" name="_csrf_token" value="<?= htmlspecialchars((string)($_SESSION['_csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
				<textarea name="message" rows="2" maxlength="1000" placeholder="Nhập câu hỏi cho chatbot..." required></textarea>
				<button type="submit" class="btn search">Gửi</button>
			</form>
		</section>
	</section>
</div>
<?php } ?>

<script src="public/js/sidebar.js?v=20260404-5"></script>
<script src="public/js/hosocanhan.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="public/js/hopdong_giahan.js"></script>
<script src="public/js/hopdong.js"></script>
<script src="public/js/baocao.js"></script>
<script src="public/js/baohiem.js"></script>
<script src="public/js/chamcong.js"></script>
<script src="public/js/luong.js"></script>
<script src="public/js/nhanvien.js"></script>
<script src="public/js/tuyendung.js"></script>
<script src="public/js/form-validation.js"></script>
<script src="public/js/chatbot.js?v=20260405-3"></script>
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

<script>
/* ====== GLOBAL UI SETTINGS ====== */
(function () {
	var root = document.documentElement;
	var i18n = {
		vi: {
			'home.dashboard_title': 'Dashboard',
			'home.dashboard_subtitle': 'Quản lý tổng quan hệ thống nhân sự',
			'home.search_employee_placeholder': '🔎 Tìm nhân viên...',
			'home.notifications_title': 'Thông báo',
			'home.notifications_new': 'Thông báo mới',
			'home.notifications_mark_all_read': 'Đánh dấu đã đọc tất cả',
			'home.no_notifications': '✅ Không có thông báo',
			'home.stats.staff': 'Nhân sự',
			'home.stats.total_staff': 'Tổng nhân viên',
			'home.stats.departments': 'Phòng ban',
			'home.stats.leave_requests': 'Đơn phép',
			'home.stats.pending_leave': 'Đơn nghỉ chờ duyệt',
			'home.stats.candidates': 'Ứng viên',
			'home.chart.staff_by_dept': '📊 Nhân viên theo phòng ban',
			'home.chart.staff_by_gender': '👨‍💼 Nhân viên theo giới tính',
			'home.chart.avg_salary_by_dept': '💰 Lương trung bình phòng ban',
			'home.dashboard.employee': 'Nhân viên',
			'home.dashboard.department': 'Phòng ban',
			'home.dashboard.salary': 'Lương',
			'home.dashboard.attendance': 'Chấm công',
			'home.dashboard.contract': 'Hợp đồng',
			'home.dashboard.report': 'Báo cáo',
			'home.dashboard.total_prefix': 'Tổng',
			'home.dashboard.salary_meta': 'Kiểm tra bảng lương',
			'home.dashboard.attendance_meta': 'Quản lý ngày công',
			'home.dashboard.contract_meta': 'Theo dõi hợp đồng',
			'home.dashboard.report_meta': 'Báo cáo hiệu suất',
			'home.view_details': 'Xem chi tiết',
			'brand.title': 'HRM SYSTEM',
			'menu.home': 'Trang chủ',
			'menu.settings': 'Cài đặt',
			'menu.hr': 'Nhân sự',
			'menu.employee': 'Nhân viên',
			'menu.profile': 'Hồ sơ',
			'menu.assignment': 'Công tác',
			'menu.contract': 'Hợp đồng',
			'menu.recruitment': 'Tuyển dụng',
			'menu.training': 'Đào tạo',
			'menu.benefits': 'Công & Phúc lợi',
			'menu.attendance': 'Chấm công',
			'menu.salary': 'Lương',
			'menu.leave': 'Nghỉ phép',
			'menu.insurance': 'Bảo hiểm',
			'menu.reward': 'Khen thưởng',
			'menu.system': 'Hệ thống',
			'menu.department': 'Phòng ban',
			'menu.position': 'Chức vụ',
			'menu.salary_grade': 'Ngạch lương',
			'menu.salary_step': 'Bậc lương',
			'menu.account': 'Tài khoản',
			'menu.permission': 'Phân quyền',
			'menu.report': 'Báo cáo',
			'theme.switch_title': 'Chuyển chế độ sáng/tối',
			'theme.dark_mode': 'Chế độ tối',
			'theme.light_mode': 'Chế độ sáng',
			'theme.enable_dark': 'Bật chế độ tối',
			'theme.enable_light': 'Chuyển sang chế độ sáng',
			'theme.light': 'Sáng',
			'theme.dark': 'Tối',
			'settings.title': 'Cài đặt hệ thống',
			'settings.appearance': 'Giao diện',
			'settings.appearance_note': 'Chọn chế độ sáng/tối cho toàn bộ hệ thống. Tùy chọn được lưu tự động cho các lần truy cập sau.',
			'settings.current_theme': 'Chế độ hiện tại:',
			'settings.density': 'Mật độ hiển thị',
			'settings.density_note': 'Điều chỉnh khoảng cách trên bảng dữ liệu ở toàn bộ hệ thống.',
			'settings.table_density': 'Mật độ bảng:',
			'settings.notifications': 'Thông báo',
			'settings.notifications_note': 'Bật/tắt hiển thị thông báo nổi và badge thông báo.',
			'settings.show_notifications': 'Hiển thị thông báo:',
			'settings.language': 'Ngôn ngữ',
			'settings.language_note': 'Lưu tùy chọn ngôn ngữ giao diện để đồng bộ cho các trang. (Bản dịch chi tiết sẽ mở rộng dần)',
			'settings.language_label': 'Ngôn ngữ:',
			'density.comfortable': 'Thoải mái',
			'density.compact': 'Gọn',
			'common.on': 'Bật',
			'common.off': 'Tắt',
			'common.search': 'Tìm kiếm',
			'common.refresh': 'Làm mới',
			'common.stt': 'STT',
			'common.employee_code': 'Mã NV',
			'common.employee': 'Nhân viên',
			'common.full_name': 'Họ tên',
			'common.gender': 'Giới tính',
			'common.date_of_birth': 'Ngày sinh',
			'common.email': 'Email',
			'common.phone': 'Điện thoại',
			'common.salary_step': 'Bậc lương',
			'common.status': 'Trạng thái',
			'common.actions': 'Thao tác',
			'common.department': 'Phòng ban',
			'common.position': 'Chức vụ',
			'common.from_date': 'Từ ngày',
			'common.to_date': 'Đến ngày',
			'common.type': 'Loại',
			'common.prev': '← Trước',
			'common.next': 'Sau →',
			'common.page': 'Trang',
			'common.present': 'Hiện tại',
			'common.not_entered': 'Chưa nhập',
			'common.not_available': 'Chưa có',
			'employee_page.title': '👥 Quản lý Nhân viên',
			'employee_page.add': '➕ Thêm nhân viên',
			'employee_page.search_placeholder': '🔍 Nhập tên nhân viên...',
			'employee_page.edit_title': 'Chỉnh sửa nhân viên',
			'employee_page.delete_title': 'Xóa nhân viên',
			'employee_page.empty': 'Không có nhân viên',
			'profile_page.title': '👥 Danh sách hồ sơ nhân viên',
			'profile_page.add': '➕ Thêm hồ sơ',
			'profile_page.search_placeholder': '🔍 Tìm theo mã NV, tên, phòng ban...',
			'profile_page.view_title': 'Xem chi tiết',
			'profile_page.edit_title': 'Chỉnh sửa',
			'profile_page.delete_title': 'Xóa',
			'profile_page.empty': 'Không có dữ liệu hồ sơ nào.',
			'assignment_page.title': '📌 Phân công nhân viên',
			'assignment_page.add': '➕ Phân công mới',
			'assignment_page.search_placeholder': '🔍 Tìm mã NV, tên, phòng ban, chức vụ...',
			'assignment_page.edit_title': 'Chỉnh sửa',
			'assignment_page.delete_title': 'Xóa',
			'assignment_page.history_title': 'Lịch sử',
			'assignment_page.empty': 'Chưa có dữ liệu phân công',
			'language.vi': 'Tiếng Việt',
			'language.en': 'English'
		},
		en: {
			'home.dashboard_title': 'Dashboard',
			'home.dashboard_subtitle': 'HR system overview and operational insights',
			'home.search_employee_placeholder': '🔎 Search employee...',
			'home.notifications_title': 'Notifications',
			'home.notifications_new': 'New notifications',
			'home.notifications_mark_all_read': 'Mark all as read',
			'home.no_notifications': '✅ No notifications',
			'home.stats.staff': 'Staff',
			'home.stats.total_staff': 'Total employees',
			'home.stats.departments': 'Departments',
			'home.stats.leave_requests': 'Leave requests',
			'home.stats.pending_leave': 'Pending leave requests',
			'home.stats.candidates': 'Candidates',
			'home.chart.staff_by_dept': '📊 Employees by department',
			'home.chart.staff_by_gender': '👨‍💼 Employees by gender',
			'home.chart.avg_salary_by_dept': '💰 Average salary by department',
			'home.dashboard.employee': 'Employees',
			'home.dashboard.department': 'Departments',
			'home.dashboard.salary': 'Payroll',
			'home.dashboard.attendance': 'Attendance',
			'home.dashboard.contract': 'Contracts',
			'home.dashboard.report': 'Reports',
			'home.dashboard.total_prefix': 'Total',
			'home.dashboard.salary_meta': 'Review payroll data',
			'home.dashboard.attendance_meta': 'Track attendance records',
			'home.dashboard.contract_meta': 'Monitor contracts',
			'home.dashboard.report_meta': 'Performance reporting',
			'home.view_details': 'View details',
			'brand.title': 'HRM SYSTEM',
			'menu.home': 'Home',
			'menu.settings': 'Settings',
			'menu.hr': 'Human Resources',
			'menu.employee': 'Employees',
			'menu.profile': 'Profiles',
			'menu.assignment': 'Assignments',
			'menu.contract': 'Contracts',
			'menu.recruitment': 'Recruitment',
			'menu.training': 'Training',
			'menu.benefits': 'Attendance & Benefits',
			'menu.attendance': 'Attendance',
			'menu.salary': 'Payroll',
			'menu.leave': 'Leave',
			'menu.insurance': 'Insurance',
			'menu.reward': 'Rewards',
			'menu.system': 'System',
			'menu.department': 'Departments',
			'menu.position': 'Positions',
			'menu.salary_grade': 'Salary Grades',
			'menu.salary_step': 'Salary Steps',
			'menu.account': 'Accounts',
			'menu.permission': 'Permissions',
			'menu.report': 'Reports',
			'theme.switch_title': 'Switch light/dark mode',
			'theme.dark_mode': 'Dark mode',
			'theme.light_mode': 'Light mode',
			'theme.enable_dark': 'Enable dark mode',
			'theme.enable_light': 'Switch to light mode',
			'theme.light': 'Light',
			'theme.dark': 'Dark',
			'settings.title': 'System Settings',
			'settings.appearance': 'Appearance',
			'settings.appearance_note': 'Choose light or dark mode for the whole system. Your preference is saved automatically.',
			'settings.current_theme': 'Current mode:',
			'settings.density': 'Display Density',
			'settings.density_note': 'Adjust spacing for data tables across the system.',
			'settings.table_density': 'Table density:',
			'settings.notifications': 'Notifications',
			'settings.notifications_note': 'Turn floating alerts and notification badges on or off.',
			'settings.show_notifications': 'Show notifications:',
			'settings.language': 'Language',
			'settings.language_note': 'Store language preference system-wide. (Full translation will be expanded gradually)',
			'settings.language_label': 'Language:',
			'density.comfortable': 'Comfortable',
			'density.compact': 'Compact',
			'common.on': 'On',
			'common.off': 'Off',
			'common.search': 'Search',
			'common.refresh': 'Refresh',
			'common.stt': 'No.',
			'common.employee_code': 'Employee ID',
			'common.employee': 'Employee',
			'common.full_name': 'Full name',
			'common.gender': 'Gender',
			'common.date_of_birth': 'Date of birth',
			'common.email': 'Email',
			'common.phone': 'Phone',
			'common.salary_step': 'Salary step',
			'common.status': 'Status',
			'common.actions': 'Actions',
			'common.department': 'Department',
			'common.position': 'Position',
			'common.from_date': 'From date',
			'common.to_date': 'To date',
			'common.type': 'Type',
			'common.prev': '← Previous',
			'common.next': 'Next →',
			'common.page': 'Page',
			'common.present': 'Present',
			'common.not_entered': 'Not entered',
			'common.not_available': 'Not available',
			'employee_page.title': '👥 Employee Management',
			'employee_page.add': '➕ Add employee',
			'employee_page.search_placeholder': '🔍 Enter employee name...',
			'employee_page.edit_title': 'Edit employee',
			'employee_page.delete_title': 'Delete employee',
			'employee_page.empty': 'No employees',
			'profile_page.title': '👥 Employee Profile List',
			'profile_page.add': '➕ Add profile',
			'profile_page.search_placeholder': '🔍 Search by employee ID, name, department...',
			'profile_page.view_title': 'View details',
			'profile_page.edit_title': 'Edit',
			'profile_page.delete_title': 'Delete',
			'profile_page.empty': 'No profile data.',
			'assignment_page.title': '📌 Employee Assignments',
			'assignment_page.add': '➕ New assignment',
			'assignment_page.search_placeholder': '🔍 Search employee ID, name, department, position...',
			'assignment_page.edit_title': 'Edit',
			'assignment_page.delete_title': 'Delete',
			'assignment_page.history_title': 'History',
			'assignment_page.empty': 'No assignment data yet',
			'language.vi': 'Vietnamese',
			'language.en': 'English'
		}
	};

	function t(key) {
		var lang = getLanguage();
		var dict = i18n[lang] || i18n.vi;
		return dict[key] || (i18n.vi[key] || key);
	}

	var staticTextPairs = [
		{ vi: 'THÊM TÀI KHOẢN', en: 'ADD ACCOUNT' },
		{ vi: 'Tên đăng nhập', en: 'Username' },
		{ vi: 'Tên đăng nhập hiện tại:', en: 'Current username:' },
		{ vi: 'Tên đăng nhập mới:', en: 'New username:' },
		{ vi: 'Mật khẩu', en: 'Password' },
		{ vi: 'Mật khẩu hiện tại:', en: 'Current password:' },
		{ vi: 'Mật khẩu mới:', en: 'New password:' },
		{ vi: 'Xác nhận mật khẩu mới:', en: 'Confirm new password:' },
		{ vi: 'Mật khẩu xác nhận:', en: 'Password confirmation:' },
		{ vi: 'Vai trò', en: 'Role' },
		{ vi: 'Mã nhân viên', en: 'Employee code' },
		{ vi: 'Mã nhân sự', en: 'Employee code' },
		{ vi: 'Hủy', en: 'Cancel' },
		{ vi: 'Tìm', en: 'Search' },
		{ vi: 'Thao tác', en: 'Actions' },
		{ vi: 'Chỉ xem', en: 'View only' },
		{ vi: 'Không có dữ liệu', en: 'No data' },
		{ vi: 'Bảo mật tài khoản', en: 'Account security' },
		{ vi: 'Tên đăng nhập', en: 'Username' },
		{ vi: 'Phiên đăng nhập', en: 'Login sessions' },
		{ vi: 'Trạng thái mật khẩu:', en: 'Password status:' },
		{ vi: 'Đang dùng mật khẩu tạm', en: 'Using temporary password' },
		{ vi: 'Ổn định', en: 'Stable' },
		{ vi: 'Làm mới phiên', en: 'Refresh session' },
		{ vi: 'Đăng xuất phiên khác', en: 'Sign out other sessions' },
		{ vi: 'Thiết bị đăng nhập gần đây', en: 'Recent sign-in devices' },
		{ vi: 'Phiên hiện tại', en: 'Current session' },
		{ vi: 'Đã đăng xuất', en: 'Signed out' },
		{ vi: 'Đang hoạt động', en: 'Active' },
		{ vi: 'Cài đặt tài khoản', en: 'Account settings' },
		{ vi: 'Đăng xuất', en: 'Sign out' },
		{ vi: 'Công cụ nhanh cho Admin', en: 'Admin quick tools' },
		{ vi: 'Sức khỏe hệ thống', en: 'System health' },
		{ vi: 'Nhật ký hệ thống', en: 'System logs' },
		{ vi: 'Phân quyền', en: 'Permissions' },
		{ vi: 'Cập nhật mật khẩu', en: 'Update password' },
		{ vi: 'Cập nhật tên đăng nhập', en: 'Update username' },
		{ vi: 'Không có dữ liệu bảo hiểm', en: 'No insurance data' },
		{ vi: 'Không có phòng ban', en: 'No departments' },
		{ vi: 'Không có dữ liệu chức vụ.', en: 'No position data.' },
		{ vi: '📅 Bảng chấm công theo tháng', en: '📅 Monthly attendance board' },
		{ vi: '📥 Xuất Excel', en: '📥 Export Excel' },
		{ vi: '🏢 Quản lý Phòng ban', en: '🏢 Department Management' },
		{ vi: '🙍‍♂️ Quản lý Chức vụ', en: '🙍‍♂️ Position Management' },
		{ vi: '🛡️ Quản lý Bảo hiểm', en: '🛡️ Insurance Management' },
		{ vi: '📆 Quản lý Nghỉ phép', en: '📆 Leave Management' },
		{ vi: '📄 Quản lý Hợp đồng', en: '📄 Contract Management' },
		{ vi: '💰 Bảng Lương', en: '💰 Payroll' },
		{ vi: '➕ Thêm', en: '➕ Add' },
		{ vi: '💾 Lưu', en: '💾 Save' },
		{ vi: '↩️ Quay lại', en: '↩️ Back' }
	];

	var staticPlaceholderPairs = [
		{ vi: 'VD: nguyenvana', en: 'Ex: john.doe' },
		{ vi: 'Tối thiểu 6 ký tự', en: 'At least 6 characters' },
		{ vi: 'VD: NV001', en: 'Ex: EMP001' },
		{ vi: 'Nhập mật khẩu hiện tại', en: 'Enter current password' },
		{ vi: 'Nhập mật khẩu mới', en: 'Enter new password' },
		{ vi: 'Nhập lại mật khẩu mới', en: 'Re-enter new password' },
		{ vi: 'Ví dụ: nguyenvana', en: 'Example: john.doe' },
		{ vi: 'Nhập mật khẩu hiện tại để xác nhận', en: 'Enter current password to confirm' },
		{ vi: '🔍 Nhập tên nhân viên...', en: '🔍 Enter employee name...' },
		{ vi: '🔍 Tìm tên phòng ban...', en: '🔍 Search department name...' },
		{ vi: '🔍 Tìm theo mã / tên chức vụ...', en: '🔍 Search by code / position name...' }
	];

	function normalizeText(text) {
		return (text || '').replace(/\s+/g, ' ').trim();
	}

	function translateStaticByPairs(text, language, pairs) {
		var normalized = normalizeText(text);
		for (var i = 0; i < pairs.length; i++) {
			var viText = normalizeText(pairs[i].vi);
			var enText = normalizeText(pairs[i].en);
			if (normalized === viText || normalized === enText) {
				return language === 'en' ? pairs[i].en : pairs[i].vi;
			}
		}
		return text;
	}

	function applyStaticFallbackTranslation(language) {
		var candidates = document.querySelectorAll('.main-content h1, .main-content h2, .main-content h3, .main-content label, .main-content th, .main-content .btn, .main-content .table-empty, .main-content .muted-inline-note');
		candidates.forEach(function (el) {
			if (el.hasAttribute('data-i18n')) return;
			var source = el.textContent;
			var translated = translateStaticByPairs(source, language, staticTextPairs);
			if (translated !== source) {
				el.textContent = translated;
			}
		});

		var placeholders = document.querySelectorAll('.main-content input[placeholder], .main-content textarea[placeholder]');
		placeholders.forEach(function (el) {
			if (el.hasAttribute('data-i18n-placeholder')) return;
			var source = el.getAttribute('placeholder') || '';
			var translated = translateStaticByPairs(source, language, staticPlaceholderPairs);
			if (translated !== source) {
				el.setAttribute('placeholder', translated);
			}
		});
	}

	function applyLanguage(language) {
		root.lang = language === 'en' ? 'en' : 'vi';

		var textNodes = document.querySelectorAll('[data-i18n]');
		textNodes.forEach(function (el) {
			var key = el.getAttribute('data-i18n');
			if (key) el.textContent = t(key);
		});

		var titleNodes = document.querySelectorAll('[data-i18n-title]');
		titleNodes.forEach(function (el) {
			var key = el.getAttribute('data-i18n-title');
			if (key) el.setAttribute('title', t(key));
		});

		var placeholderNodes = document.querySelectorAll('[data-i18n-placeholder]');
		placeholderNodes.forEach(function (el) {
			var key = el.getAttribute('data-i18n-placeholder');
			if (key) el.setAttribute('placeholder', t(key));
		});

		var ariaLabelNodes = document.querySelectorAll('[data-i18n-aria-label]');
		ariaLabelNodes.forEach(function (el) {
			var key = el.getAttribute('data-i18n-aria-label');
			if (key) el.setAttribute('aria-label', t(key));
		});

		applyStaticFallbackTranslation(language === 'en' ? 'en' : 'vi');
	}

	function getTheme() {
		return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
	}

	function getDensity() {
		return root.getAttribute('data-density') === 'compact' ? 'compact' : 'comfortable';
	}

	function getNotifications() {
		return root.classList.contains('notifications-off') ? 'off' : 'on';
	}

	function getLanguage() {
		return root.getAttribute('data-language') === 'en' ? 'en' : 'vi';
	}

	function applySettings(settings) {
		if (settings.theme) {
			var theme = settings.theme === 'dark' ? 'dark' : 'light';
			root.setAttribute('data-theme', theme);
			try { localStorage.setItem('hrm-theme', theme); } catch (e) {}
		}

		if (settings.density) {
			var density = settings.density === 'compact' ? 'compact' : 'comfortable';
			root.setAttribute('data-density', density);
			try { localStorage.setItem('hrm-density', density); } catch (e) {}
		}

		if (settings.notifications) {
			var notifications = settings.notifications === 'off' ? 'off' : 'on';
			if (notifications === 'off') {
				root.classList.add('notifications-off');
			} else {
				root.classList.remove('notifications-off');
			}
			try { localStorage.setItem('hrm-notifications', notifications); } catch (e) {}
		}

		if (settings.language) {
			var language = settings.language === 'en' ? 'en' : 'vi';
			root.setAttribute('data-language', language);
			try { localStorage.setItem('hrm-language', language); } catch (e) {}
			document.cookie = 'hrm-language=' + encodeURIComponent(language) + '; path=/; max-age=31536000; samesite=lax';
			applyLanguage(language);
		}

		var btn = document.getElementById('darkModeToggle');
		if (btn) {
			var label = btn.querySelector('.toggle-label');
			if (label) label.textContent = getTheme() === 'dark' ? t('theme.light_mode') : t('theme.dark_mode');
		}
	}

	window.HRMSettings = {
		get: function () {
			return {
				theme: getTheme(),
				density: getDensity(),
				notifications: getNotifications(),
				language: getLanguage()
			};
		},
		apply: applySettings
	};

	// Sync sidebar dark mode toggle
	var sidebarBtn = document.getElementById('darkModeToggle');
	if (sidebarBtn) {
		applySettings({});
		sidebarBtn.addEventListener('click', function () {
			var current = getTheme();
			applySettings({ theme: current === 'dark' ? 'light' : 'dark' });
		});
	}

	applyLanguage(getLanguage());

	// Init controls on Settings page if present
	var settingsToggleBtn = document.getElementById('settingsThemeToggle');
	var settingsStatus = document.getElementById('settingsThemeStatus');
	var settingsDensity = document.getElementById('settingsDensity');
	var settingsNotifications = document.getElementById('settingsNotifications');
	var settingsLanguage = document.getElementById('settingsLanguage');

	function syncSettingsUi() {
		if (!settingsToggleBtn || !settingsStatus) {
			return;
		}

			var state = window.HRMSettings.get();
			var isDark = state.theme === 'dark';

			settingsStatus.textContent = isDark ? t('theme.dark') : t('theme.light');
			settingsStatus.className = isDark ? 'status-badge warning' : 'status-badge info';
			settingsToggleBtn.textContent = isDark ? t('theme.enable_light') : t('theme.enable_dark');

			if (settingsDensity) settingsDensity.value = state.density;
			if (settingsNotifications) settingsNotifications.value = state.notifications;
			if (settingsLanguage) settingsLanguage.value = state.language;
	}

	if (settingsToggleBtn && settingsStatus) {

		syncSettingsUi();

		settingsToggleBtn.addEventListener('click', function () {
			var current = window.HRMSettings.get().theme;
			window.HRMSettings.apply({ theme: current === 'dark' ? 'light' : 'dark' });
			syncSettingsUi();
		});

		if (settingsDensity) {
			settingsDensity.addEventListener('change', function () {
				window.HRMSettings.apply({ density: settingsDensity.value });
			});
		}

		if (settingsNotifications) {
			settingsNotifications.addEventListener('change', function () {
				window.HRMSettings.apply({ notifications: settingsNotifications.value });
			});
		}

		if (settingsLanguage) {
			settingsLanguage.addEventListener('change', function () {
				window.HRMSettings.apply({ language: settingsLanguage.value });
				syncSettingsUi();
			});
		}
	}
})();
</script>
</body>
</html>