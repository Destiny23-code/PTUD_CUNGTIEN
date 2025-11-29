<?php
require_once("clsconnect.php");

class LapPYCNL extends ketnoi {
    private $conn;

    public function __construct() {
        $this->conn = $this->connect();
    }

    public function getKeHoachSanXuat() {
        try {
            $sql = "SELECT kh.maKHSX, kh.ngayLap, kh.hinhThuc,
                           dh.maDH, ct.soLuong as soLuongCanSX,
                           sp.maSP, sp.tenSP
                    FROM kehoachsanxuat kh
                    INNER JOIN donhang dh ON kh.maDH = dh.maDH
                    INNER JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
                    INNER JOIN sanpham sp ON ct.maSP = sp.maSP
                    WHERE kh.trangThai = 'Đã duyệt'
                    ORDER BY kh.ngayLap DESC, sp.tenSP";
            
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi getKeHoachSanXuat: " . $e->getMessage());
            return array();
        }
    }

    public function getKeHoachSanXuatById($maKHSX) {
        try {
            $ma = intval($maKHSX);
            if ($ma <= 0) return null;
            
            $sql = "SELECT kh.maKHSX, kh.ngayLap, kh.hinhThuc, kh.nguoiLap,
                           dh.maDH, ct.soLuong as soLuongCanSX, dh.ngayDat, dh.ngayGiaoDuKien
                    FROM kehoachsanxuat kh
                    INNER JOIN donhang dh ON kh.maDH = dh.maDH
                    INNER JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
                    WHERE kh.maKHSX = {$ma}
                    LIMIT 1";
            
            $result = $this->laydulieu($this->conn, $sql);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Lỗi getKeHoachSanXuatById: " . $e->getMessage());
            return null;
        }
    }

    public function getNguyenLieu() {
        try {
            $sql = "SELECT maNL, tenNL, donViTinh, soLuongTon 
                    FROM nguyenlieu 
                    WHERE trangThai = 'Đạt' 
                    ORDER BY tenNL";
            $result = $this->laydulieu($this->conn, $sql);
            return $result;
        } catch (Exception $e) {
            error_log("Lỗi getNguyenLieu: " . $e->getMessage());
            return array();
        }
    }
    
    public function getDinhMucNguyenLieuByMaSP($maSP) {
        try {
            $ma = intval($maSP);
            if ($ma <= 0) return array();
            
            $sql = "SELECT dm.maNL, dm.soLuongTheoSP as soLuongTrong1SP,
                           nl.tenNL, nl.donViTinh, nl.soLuongTon as tonKhoHienTai
                    FROM dinhmucnguyenlieu dm
                    INNER JOIN nguyenlieu nl ON dm.maNL = nl.maNL
                    WHERE dm.maSP = {$ma}
                    ORDER BY nl.tenNL";
            
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi getDinhMucNguyenLieuByMaSP: " . $e->getMessage());
            return array();
        }
    }
    
    public function getKeHoachSanXuatByMaSP($maKHSX, $maSP) {
        try {
            $maKH = intval($maKHSX);
            $maSPInt = intval($maSP);
            if ($maKH <= 0 || $maSPInt <= 0) return null;
            
            $sql = "SELECT kh.maKHSX, kh.ngayLap, kh.hinhThuc, kh.nguoiLap,
                           dh.maDH, ct.soLuong as soLuongCanSX, dh.ngayDat, dh.ngayGiaoDuKien
                    FROM kehoachsanxuat kh
                    INNER JOIN donhang dh ON kh.maDH = dh.maDH
                    INNER JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
                    WHERE kh.maKHSX = {$maKH} AND ct.maSP = {$maSPInt}
                    LIMIT 1";
            
            $result = $this->laydulieu($this->conn, $sql);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Lỗi getKeHoachSanXuatByMaSP: " . $e->getMessage());
            return null;
        }
    }

    public function getDinhMucNguyenLieuByKHSX($maKHSX) {
        try {
            $ma = intval($maKHSX);
            if ($ma <= 0) return array();
            
            // Lấy maSP từ kế hoạch sản xuất
            $sqlSP = "SELECT sp.maSP
                      FROM kehoachsanxuat kh
                      INNER JOIN donhang dh ON kh.maDH = dh.maDH
                      INNER JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
                      INNER JOIN sanpham sp ON ct.maSP = sp.maSP
                      WHERE kh.maKHSX = {$ma}
                      LIMIT 1";
            
            $resultSP = $this->laydulieu($this->conn, $sqlSP);
            
            if (empty($resultSP)) {
                return array();
            }
            
            $maSP = $resultSP[0]['maSP'];
            
            // Lấy định mức nguyên liệu cho sản phẩm này
            $sql = "SELECT dm.maNL, dm.soLuongTheoSP as soLuongTrong1SP,
                           nl.tenNL, nl.donViTinh, nl.soLuongTon as tonKhoHienTai
                    FROM dinhmucnguyenlieu dm
                    INNER JOIN nguyenlieu nl ON dm.maNL = nl.maNL
                    WHERE dm.maSP = {$maSP}
                    ORDER BY nl.tenNL";
            
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi getDinhMucNguyenLieuByKHSX: " . $e->getMessage());
            return array();
        }
    }

    public function getAllXuong() {
        try {
            $sql = "SELECT maXuong, tenXuong, diaChi, sDT FROM xuong ORDER BY maXuong";
            $result = $this->laydulieu($this->conn, $sql);
            return $result;
        } catch (Exception $e) {
            error_log("Lỗi getAllXuong: " . $e->getMessage());
            return array();
        }
    }

    public function getXuongById($maXuong) {
        try {
            $ma = intval($maXuong);
            if ($ma <= 0) return null;
            $sql = "SELECT maXuong, tenXuong, diaChi, sDT FROM xuong WHERE maXuong = {$ma}";
            $data = $this->laydulieu($this->conn, $sql);
            return !empty($data) ? $data[0] : null;
        } catch (Exception $e) {
            error_log("Lỗi getXuongById: " . $e->getMessage());
            return null;
        }
    }

    public function insertPhieuYeuCau($maKHSX, $nguoiLap, $maXuong, $details, $ghiChu = '') {
        if (empty($details)) return "Chi tiết yêu cầu không hợp lệ.";

        $conn = $this->conn;
        $conn->autocommit(false);
        
        try {
            $maPhieu = "YE" . date("YmdHis");
            $maKHSX_int = intval($maKHSX);
            $maXuong_int = intval($maXuong);
            $nguoiLap_esc = $conn->real_escape_string($nguoiLap);
            $ghiChu_esc = $conn->real_escape_string($ghiChu);
            
            $sql_py = "INSERT INTO phieuyeucaunguyenlieu (maPhieu, maKHSX, ngayLap, nguoiLap, trangThai, maXuong, ghiChu)
                       VALUES ('{$maPhieu}', {$maKHSX_int}, CURDATE(), '{$nguoiLap_esc}', 'Chờ duyệt', {$maXuong_int}, '{$ghiChu_esc}')";
            
            if (!$conn->query($sql_py)) {
                throw new Exception("Lỗi khi tạo phiếu: " . $conn->error);
            }

            $maPYCNL = $conn->insert_id;

            foreach ($details as $item) {
                $maNL = intval($item['maNL']);
                $soLuongStr = str_replace('.', '', $item['soLuongYeuCau']);
                $soLuong = floatval(str_replace(',', '.', $soLuongStr));
                
                if ($maNL > 0 && $soLuong > 0) {
                    $sql_ct = "INSERT INTO chitietphieuyeucaunguyenlieu (maPYCNL, maNL, soLuongYeuCau)
                               VALUES ({$maPYCNL}, {$maNL}, {$soLuong})";
                    
                    if (!$conn->query($sql_ct)) {
                        throw new Exception("Lỗi khi thêm chi tiết: " . $conn->error);
                    }
                }
            }

            $conn->commit();
            $conn->autocommit(true);
            return true;

        } catch (Exception $e) {
            $conn->rollback();
            $conn->autocommit(true);
            error_log("insertPhieuYeuCau error: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function getPhieuYeuCauById($maPYCNL) {
        try {
            $ma = intval($maPYCNL);
            if ($ma <= 0) return null;
            
            $sql = "SELECT py.*, 
                           kh.maKHSX, kh.hinhThuc,
                           dh.soLuong as soLuongCanSX,
                           sp.tenSP, sp.donViTinh as donViTinhSP,
                           x.tenXuong, x.diaChi as diaChiXuong,
                           nv.tenNV as tenNguoiLap
                    FROM phieuyeucaunguyenlieu py
                    LEFT JOIN kehoachsanxuat kh ON py.maKHSX = kh.maKHSX
                    LEFT JOIN donhang dh ON kh.maDH = dh.maDH
                    LEFT JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
                    LEFT JOIN sanpham sp ON ct.maSP = sp.maSP
                    LEFT JOIN xuong x ON py.maXuong = x.maXuong
                    LEFT JOIN nhanvien nv ON py.nguoiLap = nv.maNV
                    WHERE py.maPYCNL = {$ma}
                    LIMIT 1";
            
            $result = $this->laydulieu($this->conn, $sql);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Lỗi getPhieuYeuCauById: " . $e->getMessage());
            return null;
        }
    }

    public function getChiTietPhieuYeuCau($maPYCNL) {
        try {
            $ma = intval($maPYCNL);
            if ($ma <= 0) return array();
            
            $sql = "SELECT ct.*, 
                           nl.tenNL, nl.donViTinh, nl.soLuongTon
                    FROM chitietphieuyeucaunguyenlieu ct
                    JOIN nguyenlieu nl ON ct.maNL = nl.maNL
                    WHERE ct.maPYCNL = {$ma}
                    ORDER BY ct.maCTPYCNL";
            
            $result = $this->laydulieu($this->conn, $sql);
            return $result;
        } catch (Exception $e) {
            error_log("Lỗi getChiTietPhieuYeuCau: " . $e->getMessage());
            return array();
        }
    }

    public function testConnection() {
        if ($this->conn && $this->conn->ping()) {
            return "Kết nối database thành công";
        } else {
            return "Lỗi kết nối database";
        }
    }
}
?>