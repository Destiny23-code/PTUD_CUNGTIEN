<?php
include_once('clsconnect.php'); 

class LichLamViec extends ketnoi {
    public function getCaLam(){
        $link = $this->connect();
        $sql = "select maCa, tenCa, gioBatDau, gioKetThuc from calam";
        $data = $this->laydulieu($link, $sql);
        return $data;
    }
      // Lấy ngoại lệ của nhân viên trong khoảng ngày
    public function getNgoaiLe($maNV, $tuNgay, $denNgay){
        $link = $this->connect();
        $sql = "SELECT maCa, ngay, trangThai, gioTangCaBatDau, gioTangCaKetThuc, ghiChu 
                FROM lichlamviec 
                WHERE maNV = '$maNV' AND ngay BETWEEN '$tuNgay' AND '$denNgay'";
        $data = $this->laydulieu($link, $sql);
        return $data;
    }
}

?>