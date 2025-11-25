<?php
require_once('clsconnect.php'); // Kết nối CSDL

class LapBCCL extends ketnoi
{

    // Thêm phiếu báo cáo chất lượng
    public function insertPhieu($ngayLap, $nguoiLap, $maLo, $maPhieu, $tieuChi, $ketQuaBaoCao)
    {
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

    // Cập nhật trạng thái phiếu yêu cầu kiểm định sau khi lập báo cáo
    public function updateTrangThaiPhieuYCKD($maPhieu, $ketQuaBaoCao)
    {
        $link = $this->connect();

        $maPhieu_safe = $link->real_escape_string($maPhieu);
        $ketQua_safe = $link->real_escape_string($ketQuaBaoCao);

        // Giữ nguyên đúng trạng thái Đạt / Không đạt
        $trangThai = ($ketQua_safe === 'Đạt') ? 'Đạt' : 'Không đạt';
        $trangThai_safe = $link->real_escape_string($trangThai);

        $sql = "UPDATE phieuyeucaukiemdinh
                SET trangThai = N'$trangThai_safe'
                WHERE maPhieu = '$maPhieu_safe'";

        if ($this->xuly($link, $sql)) {
            $link->close();
            return true;
        } else {
            echo "Lỗi cập nhật trạng thái: " . $link->error;
            $link->close();
            return false;
        }
    }

    public function kiemTraTonTaiBaoCao($maPhieu)
    {
        $conn = $this->connect();

        $sql = "SELECT * FROM phieubaocaochatluong WHERE maPhieu = '$maPhieu' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        return mysqli_num_rows($result) > 0;
    }
}
