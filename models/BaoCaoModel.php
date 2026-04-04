<?php

class BaoCaoModel{

    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    // Lấy tất cả báo cáo
    public function getAll(){

        $sql = "SELECT * FROM baocao ORDER BY ThoiDiemTao DESC";

        return mysqli_query($this->conn,$sql);
    }

    // Thêm báo cáo
    public function create($data){

        $sql = "INSERT INTO baocao
        (TenBaoCao,LoaiBaoCao,TuNgay,DenNgay,NguoiTao,GhiChu)
        VALUES
        (?,?,?,?,?,?)";

        $stmt = mysqli_prepare($this->conn,$sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssssss",
            $data['TenBaoCao'],
            $data['LoaiBaoCao'],
            $data['TuNgay'],
            $data['DenNgay'],
            $data['NguoiTao'],
            $data['GhiChu']
        );

        return mysqli_stmt_execute($stmt);
    }

    // Lấy 1 báo cáo
    public function find($id){

        $sql = "SELECT * FROM baocao WHERE MaBC=?";

        $stmt = mysqli_prepare($this->conn,$sql);

        mysqli_stmt_bind_param($stmt,"i",$id);

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt)->fetch_assoc();
    }

    // Cập nhật báo cáo
    public function update($id,$data){

        $sql="UPDATE baocao SET
        TenBaoCao=?,
        LoaiBaoCao=?,
        TuNgay=?,
        DenNgay=?,
        NguoiTao=?,
        GhiChu=?
        WHERE MaBC=?";

        $stmt=mysqli_prepare($this->conn,$sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssi",
            $data['TenBaoCao'],
            $data['LoaiBaoCao'],
            $data['TuNgay'],
            $data['DenNgay'],
            $data['NguoiTao'],
            $data['GhiChu'],
            $id
        );

        return mysqli_stmt_execute($stmt);
    }

    // Xóa báo cáo
    public function delete($id){

        $sql="DELETE FROM baocao WHERE MaBC=?";

        $stmt=mysqli_prepare($this->conn,$sql);

        mysqli_stmt_bind_param($stmt,"i",$id);

        return mysqli_stmt_execute($stmt);
    }
    // Dashboard thống kê
// Dashboard tổng quan
public function dashboard(){

    $data = [];

    $sql = "SELECT COUNT(*) as tong FROM nhanvien";
    $data['nhanvien'] = mysqli_fetch_assoc(mysqli_query($this->conn,$sql))['tong'];

    $sql = "SELECT COUNT(*) as tong FROM phongban";
    $data['phongban'] = mysqli_fetch_assoc(mysqli_query($this->conn,$sql))['tong'];

    $sql = "SELECT COUNT(*) as tong FROM hopdong";
    $data['hopdong'] = mysqli_fetch_assoc(mysqli_query($this->conn,$sql))['tong'];

    $sql = "SELECT COUNT(*) as tong FROM dottuyendung";
    $data['tuyendung'] = mysqli_fetch_assoc(mysqli_query($this->conn,$sql))['tong'];

    return $data;
}


// nhân viên theo phòng ban
public function thongKePhongBan(){

    $sql = "SELECT pb.TenPhongBan, COUNT(nv.MaNV) as tong
            FROM phongban pb
            LEFT JOIN nhanvien nv ON pb.MaPhongBan = nv.MaPhongBan
            GROUP BY pb.MaPhongBan";

    return mysqli_query($this->conn,$sql);
}


// tuyển dụng theo tháng
public function thongKeTuyenDung(){

    $sql = "SELECT MONTH(TuNgay) as thang, COUNT(*) as tong
            FROM dottuyendung
            GROUP BY MONTH(TuNgay)";

    return mysqli_query($this->conn,$sql);
}
// thống kê lương theo tháng
public function thongKeLuong(){

    $sql = "SELECT MONTH(NgayLuong) as thang, SUM(TongLuong) as tong
            FROM luong
            GROUP BY MONTH(NgayLuong)";

    return mysqli_query($this->conn,$sql);
}


// thống kê nghỉ phép
public function thongKeNghiPhep(){

    $sql = "SELECT TrangThai, COUNT(*) as tong
            FROM nghiphep
            GROUP BY TrangThai";

    return mysqli_query($this->conn,$sql);
}


// top nhân viên nghỉ nhiều
public function topNghiPhep(){

    $sql = "SELECT nv.HoTen, COUNT(np.MaNP) as tong
            FROM nghiphep np
            JOIN nhanvien nv ON np.MaNV = nv.MaNV
            GROUP BY np.MaNV
            ORDER BY tong DESC
            LIMIT 5";

    return mysqli_query($this->conn,$sql);
}
// chấm công theo tháng
public function thongKeChamCong(){

$sql="SELECT MONTH(NgayChamCong) thang,
COUNT(*) tong
FROM chamcong
GROUP BY MONTH(NgayChamCong)";

return mysqli_query($this->conn,$sql);

}


// nhân viên theo giới tính
public function thongKeGioiTinh(){

$sql="SELECT GioiTinh,COUNT(*) tong
FROM nhanvien
GROUP BY GioiTinh";

return mysqli_query($this->conn,$sql);

}


// hợp đồng theo loại
public function thongKeHopDong(){

$sql="SELECT LoaiHopDong,COUNT(*) tong
FROM hopdong
GROUP BY LoaiHopDong";

return mysqli_query($this->conn,$sql);

}
}