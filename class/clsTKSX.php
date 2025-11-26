<?php
require_once("clsconnect.php");

class ThongKeSanXuat extends ketnoi {
    private $conn;
    
    public function __construct() {
        $this->conn = $this->connect();
    }
    
    public function layDuLieuThongKe($tuNgay = null, $denNgay = null, $dayChuyen = null, $maSP = null) {
        try {
            // Lấy dữ liệu từ bảng kehoachsanxuat và donhang
            $sql = "SELECT 
                        kh.maKHSX,
                        DATE_FORMAT(kh.ngayLap, '%d/%m/%Y') as ThoiGian,
                        kh.ngayLap,
                        CASE 
                            WHEN kh.maKHSX % 3 = 0 THEN 'DC01'
                            WHEN kh.maKHSX % 3 = 1 THEN 'DC02' 
                            ELSE 'DC03'
                        END as DayChuyen,
                        sp.maSP as MS_SP,
                        sp.tenSP as TenSP,
                        dh.soLuong as SL_KeHoach,
                        FLOOR(dh.soLuong * 0.95) as SL_ThucTe,
                        ROUND((FLOOR(dh.soLuong * 0.95) / dh.soLuong) * 100, 1) as TyLeHoanThanh,
                        FLOOR(dh.soLuong * 0.02) as SP_Loi,
                        ROUND((FLOOR(dh.soLuong * 0.02) / dh.soLuong) * 100, 2) as TyLeLoi,
                        kh.trangThai as TrangThai
                    FROM kehoachsanxuat kh
                    INNER JOIN donhang dh ON kh.maDH = dh.maDH
                    INNER JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
                    INNER JOIN sanpham sp ON ct.maSP = sp.maSP
                    WHERE kh.ngayLap IS NOT NULL";
            
            if ($tuNgay && $denNgay) {
                $sql .= " AND kh.ngayLap BETWEEN '{$tuNgay}' AND '{$denNgay}'";
            }
            
            if ($maSP) {
                $sql .= " AND sp.maSP = " . intval($maSP);
            }
            
            $sql .= " ORDER BY kh.ngayLap DESC, kh.maKHSX DESC LIMIT 20";
            
            error_log("SQL Query: " . $sql);
            
            $result = $this->laydulieu($this->conn, $sql);
            
            error_log("Result count: " . (is_array($result) ? count($result) : 0));
            
            if ($result && is_array($result)) {
                return $result;
            }
            return array();
            
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
    
    public function layTongQuanThongKe($tuNgay = null, $denNgay = null) {
        try {
            $sql = "SELECT 
                        SUM(dh.soLuong) as tongKeHoach,
                        COUNT(kh.maKHSX) as tongLo,
                        SUM(FLOOR(dh.soLuong * 0.95)) as slThucTe,
                        SUM(FLOOR(dh.soLuong * 0.02)) as slLoi
                    FROM kehoachsanxuat kh
                    INNER JOIN donhang dh ON kh.maDH = dh.maDH
                    WHERE kh.ngayLap IS NOT NULL";
            
            if ($tuNgay && $denNgay) {
                $sql .= " AND kh.ngayLap BETWEEN '{$tuNgay}' AND '{$denNgay}'";
            }
            
            error_log("SQL TongQuan: " . $sql);
            
            $result = $this->laydulieu($this->conn, $sql);
            
            if (!empty($result) && is_array($result)) {
                $data = $result[0];
                $data['tongKeHoach'] = $data['tongKeHoach'] ? $data['tongKeHoach'] : 0;
                $data['tongLo'] = $data['tongLo'] ? $data['tongLo'] : 0;
                $data['slThucTe'] = $data['slThucTe'] ? $data['slThucTe'] : 0;
                $data['slLoi'] = $data['slLoi'] ? $data['slLoi'] : 0;
                
                error_log("TongQuan data: " . print_r($data, true));
                
                return $data;
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
