<?php
require_once("clsconnect.php");

class PhanCongNhanCong extends ketnoi {
    private $conn;
    
    public function __construct() {
        $this->conn = $this->connect();
    }
    
    // Lấy danh sách dây chuyền
    public function layDanhSachDayChuyen() {
        try {
            $sql = "SELECT maDC, tenDC FROM daychuyen ORDER BY maDC";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi layDanhSachDayChuyen: " . $e->getMessage());
            return array();
        }
    }
    
    // Lấy danh sách nhân viên
    public function layDanhSachNhanVien() {
        try {
            $sql = "SELECT nv.maNV, nv.tenNV, nv.sDT, nv.diaChi, ln.tenLoai
                    FROM nhanvien nv
                    LEFT JOIN loainhanvien ln ON nv.maLoai = ln.maLoai
                    ORDER BY nv.tenNV";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi layDanhSachNhanVien: " . $e->getMessage());
            return array();
        }
    }
    
    // Lấy danh sách phân công
    public function layDanhSachPhanCong($ngay = null, $maDC = null) {
        try {
            $sql = "SELECT pc.*, 
                           nv.tenNV, 
                           dc.tenDC,
                           ln.tenLoai,
                           DATE_FORMAT(pc.ngayLamViec, '%d/%m/%Y') as ngayFormat,
                           TIME_FORMAT(pc.gioBatDau, '%H:%i') as gioBDFormat,
                           TIME_FORMAT(pc.gioKetThuc, '%H:%i') as gioKTFormat
                    FROM phancong_nhancong pc
                    INNER JOIN nhanvien nv ON pc.maNV = nv.maNV
                    INNER JOIN daychuyen dc ON pc.maDC = dc.maDC
                    LEFT JOIN loainhanvien ln ON nv.maLoai = ln.maLoai
                    WHERE 1=1";
            
            if ($ngay) {
                $sql .= " AND pc.ngayLamViec = '{$ngay}'";
            }
            
            if ($maDC) {
                $sql .= " AND pc.maDC = " . intval($maDC);
            }
            
            $sql .= " ORDER BY pc.ngayLamViec DESC, pc.gioBatDau ASC";
            
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi layDanhSachPhanCong: " . $e->getMessage());
            return array();
        }
    }
    
    // Thêm phân công mới
    public function themPhanCong($maDC, $maNV, $ngayLamViec, $gioBatDau, $gioKetThuc, $ghiChu = '') {
        try {
            // Kiểm tra trùng lịch
            $sqlCheck = "SELECT COUNT(*) as total 
                        FROM phancong_nhancong 
                        WHERE maNV = " . intval($maNV) . "
                        AND ngayLamViec = '{$ngayLamViec}'
                        AND (
                            (gioBatDau <= '{$gioBatDau}' AND gioKetThuc > '{$gioBatDau}')
                            OR (gioBatDau < '{$gioKetThuc}' AND gioKetThuc >= '{$gioKetThuc}')
                            OR (gioBatDau >= '{$gioBatDau}' AND gioKetThuc <= '{$gioKetThuc}')
                        )";
            
            $checkResult = $this->laydulieu($this->conn, $sqlCheck);
            if ($checkResult && $checkResult[0]['total'] > 0) {
                return "Nhân viên đã được phân công trong khung giờ này!";
            }
            
            // Thêm phân công
            $sql = "INSERT INTO phancong_nhancong 
                    (maDC, maNV, ngayLamViec, gioBatDau, gioKetThuc, ghiChu, trangThai)
                    VALUES (
                        " . intval($maDC) . ",
                        " . intval($maNV) . ",
                        '{$ngayLamViec}',
                        '{$gioBatDau}',
                        '{$gioKetThuc}',
                        '" . $this->conn->real_escape_string($ghiChu) . "',
                        'Đã phân công'
                    )";
            
            if ($this->conn->query($sql)) {
                return true;
            } else {
                return "Lỗi: " . $this->conn->error;
            }
        } catch (Exception $e) {
            error_log("Lỗi themPhanCong: " . $e->getMessage());
            return "Lỗi: " . $e->getMessage();
        }
    }
    
    // Xóa phân công
    public function xoaPhanCong($maPC) {
        try {
            $sql = "DELETE FROM phancong_nhancong WHERE maPC = " . intval($maPC);
            if ($this->conn->query($sql)) {
                return true;
            } else {
                return "Lỗi: " . $this->conn->error;
            }
        } catch (Exception $e) {
            error_log("Lỗi xoaPhanCong: " . $e->getMessage());
            return "Lỗi: " . $e->getMessage();
        }
    }
}
?>
