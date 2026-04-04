<?php
require_once 'models/PhanQuyenModel.php';
require_once 'core/AppLogger.php';

class AuthMiddleware {

    public static function check($conn, $tenChucNang){

        // ✅ CHỈ start nếu chưa có session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if(!isset($_SESSION['MaTK'])){
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                http_response_code(401);
                echo 'SESSION_EXPIRED';
                exit;
            }

            header("Location: index.php?controller=dangnhap&action=login");
            exit;
        }

        $model = new PhanQuyenModel($conn);

        $coQuyen = $model->hasPermission($_SESSION['MaTK'], $tenChucNang);

        if(!$coQuyen){
            AppLogger::security('Permission denied', [
                'MaTK' => (int)($_SESSION['MaTK'] ?? 0),
                'function' => $tenChucNang,
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
            ]);

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                http_response_code(403);
                echo 'FORBIDDEN';
                exit;
            }

            $_SESSION['error'] = 'Bạn không có quyền truy cập chức năng này.';
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }
 public static function has($tenChucNang){

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($_SESSION['quyen'])){
        return false;
    }

    return in_array($tenChucNang, $_SESSION['quyen']);
}

 public static function isAdminSession(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $vaiTro = strtolower(trim((string)($_SESSION['taikhoan']['VaiTro'] ?? '')));
    return $vaiTro === 'admin';
 }

 public static function isAdmin($conn): bool {
    if (self::isAdminSession()) {
        return true;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $maTK = (int)($_SESSION['MaTK'] ?? 0);
    if ($maTK <= 0) {
        return false;
    }

    $model = new PhanQuyenModel($conn);
    return $model->isAdminAccount($maTK);
 }
}