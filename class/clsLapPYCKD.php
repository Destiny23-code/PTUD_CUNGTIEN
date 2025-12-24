<?php
require_once("clsconnect.php");

class clsLapPYCKD extends ketnoi {

    private $db; 

    public function __construct() {
        // Khởi tạo đối tượng kết nối
        $this->db = new ketnoi();
    }

    // File: clsLapPYCKD.php
public function getLoSanPhamByTrangThai($trangThai = 'Chưa kiểm định') {
    $conn = $this->db->connect();
    $trangThaiEscaped = $conn->real_escape_string($trangThai);
    
    // Bỏ l.tenLo vì trong SQL không có cột này
    $sql = "SELECT l.maLo, l.ngaySX as ngaySanXuat, l.soLuong, l.trangThai, s.tenSP
            FROM losanpham l
            LEFT JOIN sanpham s ON l.maSP = s.maSP
            WHERE l.trangThai = '$trangThaiEscaped'
            ORDER BY l.ngaySX DESC";
    
    $data = $this->db->laydulieu($conn, $sql);
    $conn->close();
    return $data;
}

    // ✅ Thêm phiếu yêu cầu và cập nhật trạng thái lô (Giữ nguyên)
    public function insertPhieuYeuCauKiemDinh($nguoiLap, $maLo, $ghiChu) {
        $conn = $this->db->connect();
        $success = false;
        $conn->autocommit(FALSE);

        try {
            $nguoiLap = $conn->real_escape_string($nguoiLap);
            $maLo = $conn->real_escape_string($maLo);
            $ghiChu = $conn->real_escape_string($ghiChu);

            $sql_insert = "INSERT INTO phieuyeucaukiemdinh (ngayLap, nguoiLap, maLo, tieuChi, trangThai)
                           VALUES (CURDATE(), '$nguoiLap', '$maLo', '$ghiChu', 'Chờ kiểm định')";
            
            if ($this->db->xuly($conn, $sql_insert)) {
                $sql_update = "UPDATE losanpham 
                               SET trangThai = 'Đang kiểm định' 
                               WHERE maLo = '$maLo'";
                
                if ($this->db->xuly($conn, $sql_update)) {
                    $conn->commit();
                    $success = true;
                } else {
                    $conn->rollback();
                }
            } else {
                $conn->rollback();
            }
        } catch (Exception $e) {
            $conn->rollback();
        }

        $conn->close();
        return $success;
    }

   public function getAllPhieuYCKD() {
    $sql = "SELECT p.maPhieu as maPYCKD, 
                   p.ngayLap, 
                   p.nguoiLap, 
                   p.tieuChi as ghiChu,
                   p.maLo, -- Lấy maLo thay cho tenLo
                   s.tenSP,
                   l.ngaySX as ngaySanXuat, 
                   p.trangThai
            FROM phieuyeucaukiemdinh p
            LEFT JOIN losanpham l ON p.maLo = l.maLo
            LEFT JOIN sanpham s ON l.maSP = s.maSP
            ORDER BY p.ngayLap DESC, p.maPhieu DESC";
    
    $conn = $this->db->connect();
    $data = $this->db->laydulieu($conn, $sql);
    $conn->close();
    return $data;
}

    // ✅ Hàm lấy tên nhân viên từ ID User (Giữ nguyên)
    public function getTenNhanVien($idUser) {
        $sql = "SELECT tenNV FROM nhanvien WHERE iduser = '$idUser' LIMIT 1";
        $conn = $this->db->connect();
        $data = $this->db->laydulieu($conn, $sql);
        $conn->close();

        if (!empty($data)) {
            return $data[0]['tenNV'];
        }
        return ''; 
    }
}
?>