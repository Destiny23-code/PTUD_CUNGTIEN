<?php
require_once("clsconnect.php");

class ThongKeSanXuat extends ketnoi {
    private $conn;
    
    public function __construct() {
        $this->conn = $this->connect();
    }
    
    public function layDuLieuThongKe($tuNgay = null, $denNgay = null, $dayChuyen = null, $maSP = null) {
        try {
            $sql = "SELECT 
                        kh.maKHSX,
                        DATE_FORMAT(kh.ngayLap, '%d/%m/%Y') as ThoiGian,
                        kh.ngayLap,
                        CONCAT('DC', LPAD(pb.maDC, 3, '0')) as DayChuyen,
                        pb.maDC as maDC_raw,
                        sp.maSP as MS_SP,
                        sp.tenSP as TenSP,
                        ct.soLuong as SL_KeHoach,
                        pb.soLuong as SL_ThucTe,
                        ROUND((pb.soLuong / ct.soLuong) * 100, 1) as TyLeHoanThanh,
                        FLOOR(ct.soLuong * 0.02) as SP_Loi,
                        ROUND((FLOOR(ct.soLuong * 0.02) / ct.soLuong) * 100, 2) as TyLeLoi,
                        kh.trangThai as TrangThai
                    FROM kehoachsanxuat kh
                    INNER JOIN donhang dh ON kh.maDH = dh.maDH
                    INNER JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
                    INNER JOIN sanpham sp ON ct.maSP = sp.maSP
                    LEFT JOIN phanbodaychuyen pb ON kh.maKHSX = pb.maKHSX AND sp.maSP = pb.maSP
                    WHERE kh.ngayLap IS NOT NULL 
                      AND kh.trangThai IN ('Đã duyệt', 'Hoàn thành', 'Đang thực hiện')
                      AND pb.maDC IS NOT NULL";
            
            if ($tuNgay && $denNgay) {
                $sql .= " AND kh.ngayLap BETWEEN '{$tuNgay}' AND '{$denNgay}'";
            }
            
            if ($dayChuyen && !empty($dayChuyen)) {
                // Lấy số từ DC001 -> 1
                $maDC = intval(str_replace('DC', '', $dayChuyen));
                $sql .= " AND pb.maDC = {$maDC}";
            }
            
            if ($maSP) {
                $sql .= " AND sp.maSP = " . intval($maSP);
            }
            
            $sql .= " ORDER BY kh.ngayLap DESC, kh.maKHSX DESC LIMIT 50";
            
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
            
        } catch (Exception $e) {
            error_log("Lỗi layDuLieuThongKe: " . $e->getMessage());
            return array();
        }
    }
    
    public function layDanhSachSanPham() {
        try {
            $sql = "SELECT maSP, tenSP FROM sanpham ORDER BY maSP";
            $result = $this->laydulieu($this->conn, $sql);
            if ($result && is_array($result)) {
                return $result;
            }
            return array();
        } catch (Exception $e) {
            error_log("Lỗi layDanhSachSanPham: " . $e->getMessage());
            return array();
        }
    }
    
    public function layDanhSachDayChuyen() {
        try {
            $sql = "SELECT DISTINCT maDC, tenDC FROM daychuyen ORDER BY maDC";
            $result = $this->laydulieu($this->conn, $sql);
            if ($result && is_array($result)) {
                return $result;
            }
            return array();
        } catch (Exception $e) {
            error_log("Lỗi layDanhSachDayChuyen: " . $e->getMessage());
            return array();
        }
    }
    
    public function layTongQuanThongKe($tuNgay = null, $denNgay = null) {
        try {
            $sql = "SELECT 
                        SUM(ct.soLuong) as tongKeHoach,
                        COUNT(DISTINCT kh.maKHSX) as tongLo,
                        SUM(COALESCE(pb.soLuong, FLOOR(ct.soLuong * 0.95))) as slThucTe,
                        SUM(FLOOR(ct.soLuong * 0.02)) as slLoi
                    FROM kehoachsanxuat kh
                    INNER JOIN donhang dh ON kh.maDH = dh.maDH
                    INNER JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
                    LEFT JOIN phanbodaychuyen pb ON kh.maKHSX = pb.maKHSX AND ct.maSP = pb.maSP
                    WHERE kh.ngayLap IS NOT NULL AND kh.trangThai IN ('Đã duyệt', 'Hoàn thành', 'Đang thực hiện')";
            
            if ($tuNgay && $denNgay) {
                $sql .= " AND kh.ngayLap BETWEEN '{$tuNgay}' AND '{$denNgay}'";
            }
            
            $result = $this->laydulieu($this->conn, $sql);
            
            if (!empty($result) && is_array($result)) {
                $data = $result[0];
                return array(
                    'tongKeHoach' => $data['tongKeHoach'] ? intval($data['tongKeHoach']) : 0,
                    'tongLo' => $data['tongLo'] ? intval($data['tongLo']) : 0,
                    'slThucTe' => $data['slThucTe'] ? intval($data['slThucTe']) : 0,
                    'slLoi' => $data['slLoi'] ? intval($data['slLoi']) : 0
                );
            }
            
            return array('tongKeHoach' => 0, 'tongLo' => 0, 'slThucTe' => 0, 'slLoi' => 0);
            
        } catch (Exception $e) {
            error_log("Lỗi layTongQuanThongKe: " . $e->getMessage());
            return array('tongKeHoach' => 0, 'tongLo' => 0, 'slThucTe' => 0, 'slLoi' => 0);
        }
    }
    
    public function testQuery() {
        // Hàm test để kiểm tra dữ liệu
        $sql = "SELECT COUNT(*) as total FROM kehoachsanxuat";
        $result = $this->laydulieu($this->conn, $sql);
        error_log("Total kehoachsanxuat: " . print_r($result, true));
        
        $sql2 = "SELECT COUNT(*) as total FROM donhang";
        $result2 = $this->laydulieu($this->conn, $sql2);
        error_log("Total donhang: " . print_r($result2, true));
        
        return array('kehoach' => $result, 'donhang' => $result2);
    }
}
?>
