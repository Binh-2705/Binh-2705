<?php
session_start();
require_once 'ketnoi.php';
require_once 'core/AppLogger.php';
require_once 'models/TaiKhoanModel.php';

if (empty($_SESSION['_csrf_token'])) {
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

$controller = $_GET['controller'] ?? 'home';
$action     = $_GET['action'] ?? 'index';
$controllerObj = null;

if ($controller === 'phanquyen' && $action === 'detail') {
    $action = 'xemTheoTaiKhoan';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionToken = (string)($_SESSION['_csrf_token'] ?? '');
    $requestToken = (string)($_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

    if ($sessionToken === '' || $requestToken === '' || !hash_equals($sessionToken, $requestToken)) {
        AppLogger::security('CSRF token mismatch', [
            'controller' => $controller,
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);

        if ($isAjax) {
            http_response_code(419);
            echo 'CSRF_INVALID';
            exit;
        }

        $_SESSION['error'] = 'Phiên làm việc đã thay đổi. Vui lòng thử lại.';
        $back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        header('Location: ' . $back);
        exit;
    }
}

// Chỉ yêu cầu đăng nhập nếu không phải là trang đăng nhập VÀ không phải là request AJAX
if (!isset($_SESSION['taikhoan']) && $controller != 'dangnhap' && !$isAjax) {
    header("Location: index.php?controller=dangnhap&action=login");
    exit;
}

// Nếu là AJAX mà chưa đăng nhập, trả về thông báo lỗi thay vì chuyển hướng
if (!isset($_SESSION['taikhoan']) && $isAjax) {
    echo "SESSION_EXPIRED";
    exit;
}

if (isset($_SESSION['taikhoan'])) {
    $sessionModel = new TaiKhoanModel($conn);
    $maTK = (int)($_SESSION['MaTK'] ?? 0);

    if ($maTK > 0 && empty($_SESSION['session_marker'])) {
        $_SESSION['session_marker'] = bin2hex(random_bytes(32));
        $sessionModel->registerSessionAudit(
            $maTK,
            (string)$_SESSION['session_marker'],
            (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
            (string)($_SERVER['REMOTE_ADDR'] ?? '')
        );
    }

    $sessionMarker = (string)($_SESSION['session_marker'] ?? '');
    if ($maTK > 0 && $sessionMarker !== '' && $sessionModel->isSessionRevoked($maTK, $sessionMarker)) {
        session_unset();
        session_destroy();

        if ($isAjax) {
            http_response_code(401);
            echo 'SESSION_REVOKED';
            exit;
        }

        session_start();
        $_SESSION['error'] = 'Phiên đăng nhập này đã bị đăng xuất từ cài đặt bảo mật.';
        header('Location: index.php?controller=dangnhap&action=login');
        exit;
    }

    if ($maTK > 0 && $sessionMarker !== '') {
        $sessionModel->touchSessionAudit($maTK, $sessionMarker);
    }
}

$mustChangePassword = !empty($_SESSION['must_change_password']) || !empty($_SESSION['taikhoan']['BuocDoiMatKhau']);
if (isset($_SESSION['taikhoan']) && $mustChangePassword) {
    $allowedDuringForcedChange = $controller === 'dangnhap' && in_array($action, ['doiMatKhauBatBuoc', 'dangxuat'], true);

    if (!$allowedDuringForcedChange) {
        if ($isAjax) {
            http_response_code(428);
            echo 'PASSWORD_CHANGE_REQUIRED';
            exit;
        }

        header('Location: index.php?controller=dangnhap&action=doiMatKhauBatBuoc');
        exit;
    }
}

switch ($controller) {
    case 'home':
    require_once 'controllers/HomeController.php';
    $controllerObj = new HomeController($conn);
    break;

    case 'nhanvien':
        require_once 'controllers/NhanVienController.php';
        $controllerObj = new NhanVienController($conn);
        break;

    case 'phongban':
        require_once 'controllers/PhongBanController.php';
        $controllerObj = new PhongBanController($conn);
        break;
    case 'phancong':
        require_once 'controllers/PhanCongController.php';
        $controllerObj = new PhanCongController($conn);
        break;

    case 'chamcong':
        require_once 'controllers/ChamCongController.php';
        $controllerObj = new ChamCongController($conn);
        
        break;
    case 'luong':
        require_once 'controllers/LuongController.php';
        $controllerObj = new LuongController($conn);
        break; 
    case 'hopdong':
        require_once 'controllers/HopDongController.php';
        $controllerObj = new HopDongController($conn);
        break;
    case 'chucvu':
        require_once 'controllers/ChucVuController.php';
        $controllerObj = new ChucVuController($conn);
        break;
    case 'khenthuong':
        require_once 'controllers/KhenThuongController.php';
        $controllerObj = new KhenThuongController($conn);
        break;
    case 'nghiphep':
        require_once 'controllers/NghiPhepController.php';
        $controllerObj = new NghiPhepController($conn);
        break;
    case 'hosocanhan':
        require_once 'controllers/HoSoCaNhanController.php';
        $controllerObj = new HoSoCaNhanController($conn);
        break;
    case 'tuyendung':
        require_once 'controllers/TuyenDungController.php';
        $controllerObj = new TuyenDungController($conn);
        break;
    case 'ngachluong':
    require_once 'controllers/NgachLuongController.php';
    $controllerObj = new NgachLuongController($conn);
    break;
    case 'bacluong':
    require_once 'controllers/BacLuongController.php';
    $controllerObj = new BacLuongController($conn);
    break;
    case 'daotao':
        require_once 'controllers/DaoTaoController.php';
        $controllerObj = new DaoTaoController($conn);
        break;
    case 'baocao':
        require_once 'controllers/BaoCaoController.php';
        $controllerObj = new BaoCaoController($conn);
        break;
    case 'search':
        require_once 'controllers/SearchController.php';
        $controllerObj = new SearchController($conn);
        break;
    case 'auditlog':
        require_once 'controllers/AuditLogController.php';
        $controllerObj = new AuditLogController($conn);
        break;
    case 'systemhealth':
        require_once 'controllers/SystemHealthController.php';
        $controllerObj = new SystemHealthController($conn);
        break;
    case 'chatbot':
        require_once 'controllers/ChatbotController.php';
        $controllerObj = new ChatbotController($conn);
        break;
    case 'baohiem':
    require_once 'controllers/BaoHiemController.php';
    $controllerObj = new BaoHiemController($conn);
    break;
    
 
    case 'taikhoan':
    require_once 'controllers/TaiKhoanController.php';
    $controllerObj = new TaiKhoanController($conn);
    break;
    case 'phanquyen':
        require_once 'controllers/PhanQuyenController.php';
        $controllerObj = new PhanQuyenController($conn);
        break;


   case 'dangnhap':
            require_once 'controllers/DangNhapController.php';
            $controllerObj = new DangNhapController($conn);
            break;
    default:
        AppLogger::warning('Controller not found', ['controller' => $controller]);
    http_response_code(404);
    include 'views/errors/404.php';
    exit;
}




if (method_exists($controllerObj, $action)) {
    $controllerObj->$action();
} else {
    AppLogger::warning('Action not found', ['controller' => $controller, 'action' => $action]);
    http_response_code(404);
    include 'views/errors/404.php';
    exit;
}
?>
