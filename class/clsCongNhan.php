<?php
require_once('clsconnect.php'); // file kết nối CSDL

class Congnhan extends ketnoi {
    public function getTTCN($maNV) {
        $link = $this->connect();

        // Kiểm tra tham số
        if (empty($maNV)) {
            return null;
        }

        // Truy vấn thông tin công nhân
        $sql = "SELECT nv.maNV, lnv.tenLoai, nv.gioiTinh, nv.ngayVaoLam, nv.diaChi, nv.sDT, dc.tenDC, x.tenXuong 
                FROM loainhanvien lnv JOIN nhanvien nv ON nv.maLoai = lnv.maLoai
                JOIN daychuyen dc ON nv.maDC = dc.maDC
                JOIN xuong x ON dc.maXuong = x.maXuong
                WHERE maNV = '" . $link->real_escape_string($maNV) . "'";

        $data = $this->laydulieu($link, $sql);

        $link->close();

        // Hàm laydulieu trả về mảng nhiều dòng → lấy dòng đầu tiên
        if (!empty($data) && is_array($data)) {
            return $data[0];
        } else {
            return null;
        }
    }
}
?>
