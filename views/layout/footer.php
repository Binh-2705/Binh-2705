</div> <!-- container -->

<script src="public/js/sidebar.js"></script>
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
			'language.vi': 'Tiếng Việt',
			'language.en': 'English'
		},
		en: {
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
			'language.vi': 'Vietnamese',
			'language.en': 'English'
		}
	};

	function t(key) {
		var lang = getLanguage();
		var dict = i18n[lang] || i18n.vi;
		return dict[key] || (i18n.vi[key] || key);
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

	if (settingsToggleBtn && settingsStatus) {
		function syncSettingsUi() {
			var state = window.HRMSettings.get();
			var isDark = state.theme === 'dark';

			settingsStatus.textContent = isDark ? t('theme.dark') : t('theme.light');
			settingsStatus.className = isDark ? 'status-badge warning' : 'status-badge info';
			settingsToggleBtn.textContent = isDark ? t('theme.enable_light') : t('theme.enable_dark');

			if (settingsDensity) settingsDensity.value = state.density;
			if (settingsNotifications) settingsNotifications.value = state.notifications;
			if (settingsLanguage) settingsLanguage.value = state.language;
		}

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
			});
		}
	}
})();
</script>
</body>
</html>