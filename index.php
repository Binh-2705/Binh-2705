<?php
session_start();
require_once 'ketnoi.php';

$controller = $_GET['controller'] ?? 'home';
$action = $_GET['action'] ?? 'index';

//Controller KHÔNG cần đăng nhập
$controller = $_GET['controller'] ?? 'home';
$action     = $_GET['action'] ?? 'index';

if (!isset($_SESSION['taikhoan']) && $controller != 'dangnhap') {
    header("Location: index.php?controller=dangnhap&action=login");
    exit;
}

switch ($controller) {
    case 'home':
        require_once 'controllers/HomeController.php';
        $controllerObj = new HomeController();
        break;

    case 'nhanvien':
        require_once 'controllers/NhanVienController.php';
        $controllerObj = new NhanVienController($conn);
        break;

    case 'phongban':
        require_once 'controllers/PhongBanController.php';
        $controllerObj = new PhongBanController($conn);
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
    case 'hoso':
        require_once 'controllers/HoSoCaNhanController.php';
        $controllerObj = new HoSoCaNhanController($conn);
        break;
    case 'tuyendung':
        require_once 'controllers/TuyenDungController.php';
        $controllerObj = new TuyenDungController($conn);
        break;
    case 'daotao':
        require_once 'controllers/DaoTaoController.php';
        $controllerObj = new DaoTaoController($conn);
        break;
    case 'thongke':
        require_once 'controllers/ThongKeController.php';
        $controllerObj = new ThongKeController($conn);
        break;
    case 'taikhoan':
    require_once 'controllers/TaiKhoanController.php';
    $controllerObj = new TaiKhoanController($conn);
    break;

   case 'dangnhap':
            require_once 'controllers/DangNhapController.php';
            $controllerObj = new DangNhapController($conn);
            break;
    default:
        die("Controller không tồn tại!");
}




if (method_exists($controllerObj, $action)) {
    $controllerObj->$action();
} else {
    die("Action không tồn tại!");
}
?>
