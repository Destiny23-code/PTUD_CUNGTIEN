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
                        DATE_FORMAT(pb.ngayBatDau, '%d/%m/%Y') as ThoiGian,
                        pb.ngayBatDau,
                        CONCAT('DC', LPAD(pb.maDC, 3, '0')) as DayChuyen,
                        pb.maDC as maDC_raw,
                        sp.maSP as MS_SP,
                        sp.tenSP as TenSP,
                        ct.soLuong as SL_KeHoach,
                        pb.soLuong as SL_ThucTe,
                        ROUND((pb.soLuong / ct.soLuong) * 100, 1) as TyLeHoanThanh,
                        pb.trangThai as TrangThai
                    FROM phanbodaychuyen pb
                    INNER JOIN kehoachsanxuat kh ON pb.maKHSX = kh.maKHSX
                    INNER JOIN donhang dh ON kh.maDH = dh.maDH
                    INNER JOIN chitiet_donhang ct ON dh.maDH = ct.maDH AND pb.maSP = ct.maSP
                    INNER JOIN sanpham sp ON pb.maSP = sp.maSP
                    INNER JOIN daychuyen dc ON pb.maDC = dc.maDC
                    WHERE dc.maXuong = 4";
            
            if ($tuNgay && $denNgay) {
                $sql .= " AND pb.ngayBatDau BETWEEN '{$tuNgay}' AND '{$denNgay}'";
            }
            
            if ($dayChuyen && !empty($dayChuyen)) {
                $maDC = intval(str_replace('DC', '', $dayChuyen));
                $sql .= " AND pb.maDC = {$maDC}";
            }
            
            if ($maSP) {
                $sql .= " AND sp.maSP = " . intval($maSP);
            }
            
            $sql .= " ORDER BY pb.ngayBatDau DESC, pb.maPBDC DESC";
            
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
            
        } catch (Exception $e) {
            error_log("Lỗi layDuLieuThongKe: " . $e->getMessage());
            return array();
        }
    }
    
    public function layDanhSachSanPham() {
        try {
            $sql = "SELECT DISTINCT sp.maSP, sp.tenSP 
                    FROM sanpham sp
                    INNER JOIN phanbodaychuyen pb ON sp.maSP = pb.maSP
                    INNER JOIN daychuyen dc ON pb.maDC = dc.maDC
                    WHERE dc.maXuong = 4
                    ORDER BY sp.maSP";
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
    
    public function layDanhSachDayChuyen($maXuong = 4) {
        try {
            $maXuong_int = intval($maXuong);
            $sql = "SELECT DISTINCT dc.maDC, dc.tenDC, dc.maXuong 
                    FROM daychuyen dc 
                    WHERE dc.maXuong = {$maXuong_int}
                    ORDER BY dc.maDC";
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
    
    public function layTongQuanThongKe($tuNgay = null, $denNgay = null, $dayChuyen = null, $maSP = null) {
        try {
            $sql = "SELECT 
                        SUM(ct.soLuong) as tongKeHoach,
                        SUM(pb.soLuong) as slThucTe,
                        COUNT(DISTINCT pb.maPBDC) as tongPhanBo
                    FROM phanbodaychuyen pb
                    INNER JOIN kehoachsanxuat kh ON pb.maKHSX = kh.maKHSX
                    INNER JOIN donhang dh ON kh.maDH = dh.maDH
                    INNER JOIN chitiet_donhang ct ON dh.maDH = ct.maDH AND pb.maSP = ct.maSP
                    INNER JOIN sanpham sp ON pb.maSP = sp.maSP
                    INNER JOIN daychuyen dc ON pb.maDC = dc.maDC
                    WHERE dc.maXuong = 4";
            
            if ($tuNgay && $denNgay) {
                $sql .= " AND pb.ngayBatDau BETWEEN '{$tuNgay}' AND '{$denNgay}'";
            }
            
            if ($dayChuyen && !empty($dayChuyen)) {
                $maDC = intval(str_replace('DC', '', $dayChuyen));
                $sql .= " AND pb.maDC = {$maDC}";
            }
            
            if ($maSP) {
                $sql .= " AND sp.maSP = " . intval($maSP);
            }
            
            $result = $this->laydulieu($this->conn, $sql);
            
            if (!empty($result) && is_array($result)) {
                $data = $result[0];
                return array(
                    'tongKeHoach' => $data['tongKeHoach'] ? intval($data['tongKeHoach']) : 0,
                    'slThucTe' => $data['slThucTe'] ? intval($data['slThucTe']) : 0,
                    'tongPhanBo' => $data['tongPhanBo'] ? intval($data['tongPhanBo']) : 0
                );
            }
            
            return array('tongKeHoach' => 0, 'slThucTe' => 0, 'tongPhanBo' => 0);
            
        } catch (Exception $e) {
            error_log("Lỗi layTongQuanThongKe: " . $e->getMessage());
            return array('tongKeHoach' => 0, 'slThucTe' => 0, 'tongPhanBo' => 0);
        }
    }
}
?>
