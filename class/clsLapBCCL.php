<?php
require_once('clsconnect.php'); // Kết nối CSDL

class LapBCCL extends ketnoi {
    
    // Thêm phiếu báo cáo chất lượng
    public function insertPhieu($ngayLap, $nguoiLap, $maLo, $maPhieu, $tieuChi, $ketQuaBaoCao) {
        $link = $this->connect();

        // Xử lý bảo mật
        $ngayLap_safe = $link->real_escape_string($ngayLap);
        $nguoiLap_safe = $link->real_escape_string($nguoiLap);
        $maLo_safe = $link->real_escape_string($maLo);
        $maPhieu_safe = $link->real_escape_string($maPhieu);
        $tieuChi_safe = $link->real_escape_string($tieuChi);
        $ketQua_safe = $link->real_escape_string($ketQuaBaoCao);

        // Không cần đưa maPKD vào câu lệnh INSERT
        $sql = "INSERT INTO phieubaocaochatluong
                (ngayLap, nguoiLap, maLo, maPhieu, tieuChi, ketQuaBaoCao)
                VALUES (
                    '$ngayLap_safe',
                    '$nguoiLap_safe',
                    '$maLo_safe',
                    '$maPhieu_safe',
                    N'$tieuChi_safe',
                    N'$ketQua_safe'
                )";

        if ($this->xuly($link, $sql)) {
            $id = $link->insert_id;
            $link->close();
            return $id;
        } else {
            echo "Lỗi SQL: " . $link->error; // Hiển thị lỗi chi tiết
            $link->close();
            return false;
        }
    }
}
?>