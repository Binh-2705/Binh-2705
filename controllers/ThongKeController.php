<?php
include_once 'models/ThongKeModel.php';

class ThongKeController {
    private $model;

    public function __construct($db) {
        $this->model = new ThongKeModel($db);
    }

    public function index() {
        $loai  = $_GET['loai']  ?? '';
        $thang = $_GET['thang'] ?? '';
        $maPB  = $_GET['ma_pb'] ?? '';

        $dsBaoCao   = $this->model->layTatCaBaoCao($loai, $thang, $maPB);
        $thongKeNV  = $this->model->thongKeNhanVien();
        $thongKeCC  = $this->model->thongKeChamCong($thang);
        $thongKeLuong = $this->model->thongKeLuong($thang);
        $thongKeDT  = $this->model->thongKeDaoTao();

        $GLOBALS['loai']  = $loai;
        $GLOBALS['thang'] = $thang;
        $GLOBALS['maPB']  = $maPB;

        include 'views/thongke/index.php';
    }

    public function them() {
        include 'views/thongke/them.php';
    }

    public function luu() {
        $this->model->taoBaoCao($_POST);
        header('Location: index.php?controller=thongke&action=index');
        exit;
    }

    public function sua() {
        $baoCao = $this->model->layBaoCaoTheoId($_GET['id']);
        include 'views/thongke/sua.php';
    }

    public function capnhat() {
        $this->model->suaBaoCao($_POST['id'], $_POST);
        header('Location: index.php?controller=thongke&action=index');
        exit;
    }

    public function xoa() {
        $this->model->xoaBaoCao($_GET['id']);
        header('Location: index.php?controller=thongke&action=index');
        exit;
    }

     public function chitiet() {
        $id = $_GET['id'];
        $baoCao = $this->model->layBaoCaoTheoId($id);
        $dsChiTiet = null;
        
        switch ($baoCao['loai']) {
            case 'nhanvien':
                $dsChiTiet = $this->model->layNhanVienTheoPhong($baoCao['ma_pb']);
                break;
            case 'chamcong':
                $dsChiTiet = $this->model->layChamCongTheoMaCC($baoCao['ma_pb']);
                break;
            case 'luong':
                $dsChiTiet = $this->model->layLuongTheoMaLuong($baoCao['ma_pb']);
                break;
            case 'daotao':
                $dsChiTiet = $this->model->layDaoTaoTheoPhong($baoCao['ma_pb']);
                break;
        }
        include 'views/thongke/chitiet.php';
    }

    public function exportExcelChiTiet() {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            die("Không tìm thấy ID báo cáo");
        }
        
        $baoCao = $this->model->layBaoCaoTheoId($id);
        
        if (!$baoCao) {
            die("Không tìm thấy báo cáo");
        }
        
        $dsChiTiet = null;
        switch ($baoCao['loai']) {
            case 'nhanvien':
                $dsChiTiet = $this->model->layNhanVienTheoPhong($baoCao['ma_pb']);
                break;
            case 'chamcong':
                $dsChiTiet = $this->model->layChamCongTheoMaCC($baoCao['ma_pb']);
                break;
            case 'luong':
                $dsChiTiet = $this->model->layLuongTheoMaLuong($baoCao['ma_pb']);
                break;
            case 'daotao':
                $dsChiTiet = $this->model->layDaoTaoTheoPhong($baoCao['ma_pb']);
                break;
        }
        $fileName = 'chitiet_baocao_' . $baoCao['id'] . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['CHI TIẾT BÁO CÁO']);
        fputcsv($output, ['Tiêu đề:', $baoCao['tieu_de']]);
        fputcsv($output, ['Loại báo cáo:', $this->getLoaiBaoCaoText($baoCao['loai'])]);
        fputcsv($output, ['Tháng:', $baoCao['thang'] ?? 'Không xác định']);
        fputcsv($output, ['Mã tham chiếu:', $baoCao['ma_pb'] ?? 'Không có']);
        fputcsv($output, ['Ngày tạo:', $baoCao['created_at']]);
        fputcsv($output, []);

        if ($dsChiTiet && mysqli_num_rows($dsChiTiet) > 0) {
            switch ($baoCao['loai']) {
                case 'nhanvien':
                    fputcsv($output, ['Mã NV', 'Họ tên', 'Giới tính', 'Ngày sinh', 'Chức vụ', 'Lương']);
                    while ($row = mysqli_fetch_assoc($dsChiTiet)) {
                        fputcsv($output, [
                            $row['MaNV'],
                            $row['HoTen'],
                            $row['GioiTinh'],
                            $row['NgaySinh'],
                            $row['ChucVu'],
                            number_format($row['Luong'], 0, ',', '.') . ' VND'
                        ]);
                    }
                    break;
                    
                case 'chamcong':
                    fputcsv($output, ['Mã CC', 'Mã NV', 'Họ tên', 'Tháng', 'Ngày làm', 'Ngày nghỉ', 'Ghi chú']);
                    while ($row = mysqli_fetch_assoc($dsChiTiet)) {
                        fputcsv($output, [
                            $row['MaCC'],
                            $row['MaNV'],
                            $row['HoTen'],
                            $row['Thang'],
                            $row['SoNgayLam'],
                            $row['SoNgayNghi'],
                            $row['GhiChu']
                        ]);
                    }
                    break;
                    
                case 'luong':
                    fputcsv($output, ['Mã lương', 'Mã NV', 'Họ tên', 'Tháng', 'Lương CB', 'Phụ cấp', 'Thưởng', 'Khấu trừ', 'Tổng lương']);
                    while ($row = mysqli_fetch_assoc($dsChiTiet)) {
                        fputcsv($output, [
                            $row['MaLuong'],
                            $row['MaNV'],
                            $row['HoTen'],
                            $row['Thang'],
                            number_format($row['LuongCB'], 0, ',', '.') . ' VND',
                            number_format($row['PhuCap'], 0, ',', '.') . ' VND',
                            number_format($row['Thuong'], 0, ',', '.') . ' VND',
                            number_format($row['KhauTru'], 0, ',', '.') . ' VND',
                            number_format($row['TongLuong'], 0, ',', '.') . ' VND'
                        ]);
                    }
                    break;
                    
                case 'daotao':
                    fputcsv($output, ['Tên khóa học', 'Ngày bắt đầu', 'Ngày kết thúc', 'Chi phí']);
                    while ($row = mysqli_fetch_assoc($dsChiTiet)) {
                        fputcsv($output, [
                            $row['TenKhoaHoc'],
                            $row['NgayBatDau'],
                            $row['NgayKetThuc'],
                            number_format($row['ChiPhi'], 0, ',', '.') . ' VND'
                        ]);
                    }
                    break;
            }
        } else {
            fputcsv($output, ['Không có dữ liệu chi tiết']);
        }

        if (!empty($baoCao['noi_dung'])) {
            fputcsv($output, []); 
            fputcsv($output, ['NỘI DUNG BÁO CÁO:']);
            $lines = explode("\n", $baoCao['noi_dung']);
            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    fputcsv($output, [trim($line)]);
                }
            }
        }

        fclose($output);
        exit;
    }

    private function getLoaiBaoCaoText($loai) {
        $map = [
            'nhanvien' => 'Nhân viên',
            'chamcong' => 'Chấm công',
            'luong' => 'Lương',
            'daotao' => 'Đào tạo'
        ];
        return $map[$loai] ?? $loai;
    }
}