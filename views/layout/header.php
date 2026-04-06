<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>HRM System</title>

<!-- Apply saved UI settings BEFORE render to prevent flash -->
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

<meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="public/css/sidebar.css?v=20260404-5">
<link rel="stylesheet" href="public/css/dashboard.css">
<link rel="stylesheet" href="public/css/baocao.css">
<link rel="stylesheet" href="public/css/phanquyen.css">
<link rel="stylesheet" href="public/css/security.css">
<link rel="stylesheet" href="public/css/chatbot.css?v=20260406-1">


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>

<?php
$flashMessages = [];

if (!empty($_SESSION['success'])) {
	$flashMessages[] = ['type' => 'success', 'text' => $_SESSION['success']];
	unset($_SESSION['success']);
}

if (!empty($_SESSION['error'])) {
	$flashMessages[] = ['type' => 'error', 'text' => $_SESSION['error']];
	unset($_SESSION['error']);
}

if (!empty($_SESSION['message'])) {
	$flashMessages[] = ['type' => 'info', 'text' => $_SESSION['message']];
	unset($_SESSION['message']);
}

if (!empty($_GET['msg'])) {
	$flashMessages[] = ['type' => 'info', 'text' => (string)$_GET['msg']];
}
?>

<?php if (!empty($flashMessages)) { ?>
<div class="flash-stack">
	<?php foreach ($flashMessages as $flash) { ?>
		<div class="flash-alert flash-<?php echo htmlspecialchars($flash['type']); ?>">
			<?php echo htmlspecialchars($flash['text']); ?>
		</div>
	<?php } ?>
</div>
<?php } ?>

<div class="container">