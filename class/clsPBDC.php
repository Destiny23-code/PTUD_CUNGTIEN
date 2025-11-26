<?php
require_once("clsconnect.php");

class PhanBoDayChuyen extends ketnoi {
    private $conn;
    
    public function __construct() {
        $this->conn = $this->connect();
    }
    
    // Lấy danh sách dây chuyền
    public function layDanhSachDayChuyen() {
        try {
            $sql = "SELECT dc.*, x.tenXuong
                    FROM daychuyen dc
                    LEFT JOIN xuong x ON dc.maXuong = x.maXuong
                    ORDER BY dc.maDC";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi layDanhSachDayChuyen: " . $e->getMessage());
            return array();
        }
    }
    
    // Lấy danh sách kế hoạch sản xuất chưa phân bổ
    public function layKeHoachChuaPhanBo() {
        try {
            $sql = "SELECT kh.maKHSX, kh.ngayLap, kh.hinhThuc,
                           dh.soLuong, dh.ngayGiaoDuKien,
                           sp.tenSP, sp.maSP
                    FROM kehoachsanxuat kh
                    INNER JOIN donhang dh ON kh.maDH = dh.maDH
                    INNER JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
                    INNER JOIN sanpham sp ON ct.maSP = sp.maSP
                    WHERE kh.trangThai = 'Đã duyệt'
                    ORDER BY kh.ngayLap DESC";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi layKeHoachChuaPhanBo: " . $e->getMessage());
            return array();
        }
    }
    
    // Tìm kiếm dây chuyền
    public function timKiemDayChuyen($keyword) {
        try {
            $keyword = $this->conn->real_escape_string($keyword);
            $sql = "SELECT dc.*, x.tenXuong
                    FROM daychuyen dc
                    LEFT JOIN xuong x ON dc.maXuong = x.maXuong
                    WHERE dc.maDC LIKE '%{$keyword}%'
                       OR dc.tenDC LIKE '%{$keyword}%'
                    ORDER BY dc.maDC";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi timKiemDayChuyen: " . $e->getMessage());
            return array();
        }
    }
    
    // Lấy thống kê
    public function layThongKe() {
        try {
            $sql1 = "SELECT COUNT(*) as tongDC FROM daychuyen";
            $result1 = $this->laydulieu($this->conn, $sql1);
            
            $sql2 = "SELECT COUNT(*) as tongHoatDong FROM daychuyen WHERE trangThai = 'Hoạt động'";
            $result2 = $this->laydulieu($this->conn, $sql2);
            
            return array(
                'tongDayChuyen' => $result1 ? $result1[0]['tongDC'] : 0,
                'tongHoatDong' => $result2 ? $result2[0]['tongHoatDong'] : 0
            );
        } catch (Exception $e) {
            error_log("Lỗi layThongKe: " . $e->getMessage());
            return array('tongDayChuyen' => 0, 'tongHoatDong' => 0);
        }
    }
    
    // Lấy danh sách phân bổ dây chuyền với thông tin chi tiết
    public function layDanhSachPhanBo() {
        try {
            $sql = "SELECT 
                        pb.maPBDC,
                        pb.maDC,
                        pb.maKHSX,
                        pb.maSP,
                        pb.soLuong,
                        pb.ngayBatDau,
                        pb.ngayKetThuc,
                        pb.trangThai,
                        pb.ghiChu,
                        dc.tenDC,
                        dc.trangThai as trangThaiDC,
                        sp.tenSP,
                        kh.ngayLap,
                        kh.hinhThuc
                    FROM phanbodaychuyen pb
                    INNER JOIN daychuyen dc ON pb.maDC = dc.maDC
                    INNER JOIN sanpham sp ON pb.maSP = sp.maSP
                    INNER JOIN kehoachsanxuat kh ON pb.maKHSX = kh.maKHSX
                    ORDER BY pb.ngayBatDau DESC, pb.maDC";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi layDanhSachPhanBo: " . $e->getMessage());
            return array();
        }
    }
    
    // Tìm kiếm phân bổ dây chuyền
    public function timKiemPhanBo($keyword) {
        try {
            $keyword = $this->conn->real_escape_string($keyword);
            $sql = "SELECT 
                        pb.maPBDC,
                        pb.maDC,
                        pb.maKHSX,
                        pb.maSP,
                        pb.soLuong,
                        pb.ngayBatDau,
                        pb.ngayKetThuc,
                        pb.trangThai,
                        pb.ghiChu,
                        dc.tenDC,
                        dc.trangThai as trangThaiDC,
                        sp.tenSP,
                        kh.ngayLap,
                        kh.hinhThuc
                    FROM phanbodaychuyen pb
                    INNER JOIN daychuyen dc ON pb.maDC = dc.maDC
                    INNER JOIN sanpham sp ON pb.maSP = sp.maSP
                    INNER JOIN kehoachsanxuat kh ON pb.maKHSX = kh.maKHSX
                    WHERE pb.maDC LIKE '%{$keyword}%'
                       OR dc.tenDC LIKE '%{$keyword}%'
                       OR sp.tenSP LIKE '%{$keyword}%'
                       OR pb.maKHSX LIKE '%{$keyword}%'
                    ORDER BY pb.ngayBatDau DESC, pb.maDC";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi timKiemPhanBo: " . $e->getMessage());
            return array();
        }
    }
    
    // Thêm phân bổ dây chuyền mới
    public function themPhanBo($maDC, $maKHSX, $maSP, $soLuong, $ngayBatDau, $ngayKetThuc, $ghiChu = '') {
        try {
            $maDC = $this->conn->real_escape_string($maDC);
            $maKHSX = $this->conn->real_escape_string($maKHSX);
            $maSP = $this->conn->real_escape_string($maSP);
            $soLuong = intval($soLuong);
            $ngayBatDau = $this->conn->real_escape_string($ngayBatDau);
            $ngayKetThuc = $this->conn->real_escape_string($ngayKetThuc);
            $ghiChu = $this->conn->real_escape_string($ghiChu);
            
            $sql = "INSERT INTO phanbodaychuyen (maDC, maKHSX, maSP, soLuong, ngayBatDau, ngayKetThuc, ghiChu, trangThai)
                    VALUES ('$maDC', '$maKHSX', '$maSP', $soLuong, '$ngayBatDau', '$ngayKetThuc', '$ghiChu', 'Chưa bắt đầu')";
            
            return $this->conn->query($sql);
        } catch (Exception $e) {
            error_log("Lỗi themPhanBo: " . $e->getMessage());
            return false;
        }
    }
    
    // Cập nhật trạng thái phân bổ
    public function capNhatTrangThai($maPBDC, $trangThai) {
        try {
            $maPBDC = intval($maPBDC);
            $trangThai = $this->conn->real_escape_string($trangThai);
            
            $sql = "UPDATE phanbodaychuyen SET trangThai = '$trangThai' WHERE maPBDC = $maPBDC";
            return $this->conn->query($sql);
        } catch (Exception $e) {
            error_log("Lỗi capNhatTrangThai: " . $e->getMessage());
            return false;
        }
    }
}
?>
