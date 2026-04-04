<?php

class SearchModel{

    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    public function timNhanVien($keyword){
        return $this->timTheoLike('SELECT * FROM nhanvien WHERE HoTen LIKE ?', $keyword);
    }

    public function timPhongBan($keyword){
        return $this->timTheoLike('SELECT * FROM phongban WHERE TenPB LIKE ?', $keyword);
    }

    public function timHopDong($keyword){
        return $this->timTheoLike('SELECT * FROM hopdong WHERE MaHD LIKE ?', $keyword);
    }

    private function timTheoLike($sql, $keyword){
        $stmt = $this->conn->prepare($sql);

        if(!$stmt){
            return false;
        }

        $keyword = '%' . $keyword . '%';
        $stmt->bind_param('s', $keyword);

        if(!$stmt->execute()){
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

}