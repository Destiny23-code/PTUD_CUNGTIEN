<?php
require_once("clsconnect.php");

class PhanCongNhanCong extends ketnoi {
    private $conn;
    
    public function __construct() {
        $this->conn = $this->connect();
    }
    
    // Lấy dây chuyền của xưởng trưởng đang đăng nhập
    public function layDayChuyenCuaNhanVien($maNV) {
        try {
            $sql = "SELECT maDC FROM nhanvien WHERE maNV = " . intval($maNV);
            $result = $this->laydulieu($this->conn, $sql);
            return $result && isset($result[0]['maDC']) ? $result[0]['maDC'] : null;
        } catch (Exception $e) {
            error_log("Lỗi layDayChuyenCuaNhanVien: " . $e->getMessage());
            return null;
        }
    }
    
    // Lấy xưởng của xưởng trưởng (qua dây chuyền được gán)
    public function layXuongCuaXuongTruong($maNV) {
        try {
            // Lấy dây chuyền của xưởng trưởng
            $maDC = $this->layDayChuyenCuaNhanVien($maNV);
            if (!$maDC) {
                return null;
            }
            
            // Lấy xưởng từ dây chuyền
            $sql = "SELECT maXuong FROM daychuyen WHERE maDC = " . intval($maDC);
            $result = $this->laydulieu($this->conn, $sql);
            return $result && isset($result[0]['maXuong']) ? $result[0]['maXuong'] : null;
        } catch (Exception $e) {
            error_log("Lỗi layXuongCuaXuongTruong: " . $e->getMessage());
            return null;
        }
    }
    
    // Lấy thông tin dây chuyền
    public function layThongTinDayChuyen($maDC) {
        try {
            $sql = "SELECT dc.*, x.tenXuong
                    FROM daychuyen dc
                    LEFT JOIN xuong x ON dc.maXuong = x.maXuong
                    WHERE dc.maDC = " . intval($maDC);
            $result = $this->laydulieu($this->conn, $sql);
            return $result && isset($result[0]) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Lỗi layThongTinDayChuyen: " . $e->getMessage());
            return null;
        }
    }
    
    // Lấy danh sách xưởng (CHỈ xưởng của quản đốc hoặc tất cả)
    public function layDanhSachXuong($maXuongQuanDoc = null) {
        try {
            $sql = "SELECT maXuong, tenXuong, diaChi, sDT
                    FROM xuong
                    WHERE 1=1";
            
            // Nếu là quản đốc, chỉ lấy xưởng của họ
            if ($maXuongQuanDoc !== null) {
                $sql .= " AND maXuong = " . intval($maXuongQuanDoc);
            }
            
            $sql .= " ORDER BY maXuong";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi layDanhSachXuong: " . $e->getMessage());
            return array();
        }
    }
    
    // Lấy danh sách dây chuyền theo xưởng
    public function layDanhSachDayChuyen($maDC = null, $maXuong = null) {
        try {
            $sql = "SELECT dc.maDC, dc.tenDC, dc.maXuong, x.tenXuong
                    FROM daychuyen dc
                    LEFT JOIN xuong x ON dc.maXuong = x.maXuong
                    WHERE 1=1";
            
            if ($maDC !== null) {
                $sql .= " AND dc.maDC = " . intval($maDC);
            }
            
            // Lọc theo xưởng nếu có
            if ($maXuong !== null) {
                $sql .= " AND dc.maXuong = " . intval($maXuong);
            }
            
            $sql .= " ORDER BY dc.maDC";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi layDanhSachDayChuyen: " . $e->getMessage());
            return array();
        }
    }
    
    // Lấy danh sách nhân viên theo dây chuyền (CHỈ NHÂN VIÊN TRONG DÂY CHUYỀN ĐÓ)
    public function layDanhSachNhanVien($maDC = null, $maXuong = null) {
        try {
            $sql = "SELECT nv.maNV, nv.tenNV, nv.sDT, nv.diaChi, nv.maDC, ln.tenLoai, dc.tenDC, dc.maXuong
                    FROM nhanvien nv
                    INNER JOIN loainhanvien ln ON nv.maLoai = ln.maLoai
                    INNER JOIN daychuyen dc ON nv.maDC = dc.maDC
                    WHERE 1=1";
            
            // CHỈ LẤY NHÂN VIÊN TRONG DÂY CHUYỀN CỤ THỂ
            if ($maDC !== null) {
                $sql .= " AND nv.maDC = " . intval($maDC);
            }
            
            // Lọc theo xưởng nếu cần (để quản đốc chỉ thấy nhân viên trong xưởng mình)
            if ($maXuong !== null) {
                $sql .= " AND dc.maXuong = " . intval($maXuong);
            }
            
            $sql .= " ORDER BY dc.tenDC, nv.tenNV";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi layDanhSachNhanVien: " . $e->getMessage());
            return array();
        }
    }
    
    // Lấy danh sách phân công theo xưởng
    public function layDanhSachPhanCong($ngay = null, $maDC = null, $maXuong = null) {
        try {
            $sql = "SELECT pc.*, 
                           nv.tenNV, 
                           dc.tenDC,
                           dc.maDC as maDayChuyen,
                           dc.maXuong,
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
            
            if ($maXuong !== null) {
                $sql .= " AND dc.maXuong = " . intval($maXuong);
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
    public function themPhanCong($maDC, $maNV, $ngayLamViec, $gioBatDau, $gioKetThuc, $ghiChu = '', $maXuongQuanDoc = null) {
        try {
            // Kiểm tra dây chuyền có thuộc xưởng của quản đốc không
            if ($maXuongQuanDoc !== null) {
                $sqlCheckXuong = "SELECT maXuong FROM daychuyen WHERE maDC = " . intval($maDC);
                $resultXuong = $this->laydulieu($this->conn, $sqlCheckXuong);
                
                if (!$resultXuong || $resultXuong[0]['maXuong'] != $maXuongQuanDoc) {
                    return "Bạn chỉ có thể phân công cho dây chuyền trong xưởng của mình!";
                }
            }
            
            // Kiểm tra nhân viên có thuộc xưởng này không (không cần cùng dây chuyền)
            $sqlCheckXuongNV = "SELECT dc.maXuong 
                                FROM nhanvien nv
                                INNER JOIN daychuyen dc ON nv.maDC = dc.maDC
                                WHERE nv.maNV = " . intval($maNV);
            $resultXuongNV = $this->laydulieu($this->conn, $sqlCheckXuongNV);
            
            $sqlCheckXuongDC = "SELECT maXuong FROM daychuyen WHERE maDC = " . intval($maDC);
            $resultXuongDC = $this->laydulieu($this->conn, $sqlCheckXuongDC);
            
            if ($resultXuongNV && $resultXuongDC) {
                $maXuongNV = $resultXuongNV[0]['maXuong'];
                $maXuongDC = $resultXuongDC[0]['maXuong'];
                
                if ($maXuongNV != $maXuongDC) {
                    return "Nhân viên này không thuộc xưởng của dây chuyền đang chọn!";
                }
            } else {
                return "Không tìm thấy thông tin nhân viên hoặc dây chuyền!";
            }
            
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
