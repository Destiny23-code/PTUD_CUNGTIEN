<?php
require_once("clsconnect.php");

class PhanBoDayChuyen extends ketnoi {
    private $conn;
    
    public function __construct() {
        $this->conn = $this->connect();
    }
    
    // Lấy danh sách dây chuyền (CHỈ XƯỞNG 4)
    public function layDanhSachDayChuyen() {
        try {
            $sql = "SELECT dc.*, x.tenXuong
                    FROM daychuyen dc
                    LEFT JOIN xuong x ON dc.maXuong = x.maXuong
                    WHERE dc.maXuong = 4
                    ORDER BY dc.maDC";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi layDanhSachDayChuyen: " . $e->getMessage());
            return array();
        }
    }
    
    // Lấy danh sách kế hoạch sản xuất đã duyệt
    public function layKeHoachChuaPhanBo() {
        try {
            $sql = "SELECT DISTINCT
                           kh.maKHSX, 
                           kh.ngayLap, 
                           kh.hinhThuc,
                           kh.maDH,
                           ct.soLuong, 
                           dh.ngayGiaoDuKien,
                           sp.tenSP, 
                           sp.maSP,
                           dh.maKH
                    FROM kehoachsanxuat kh
                    INNER JOIN donhang dh ON kh.maDH = dh.maDH
                    INNER JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
                    INNER JOIN sanpham sp ON ct.maSP = sp.maSP
                    WHERE kh.trangThai = 'Đã duyệt'
                    ORDER BY kh.ngayLap DESC, sp.tenSP";
            
            $result = $this->laydulieu($this->conn, $sql);
            
            // Debug log
            if ($result === false || $result === null) {
                error_log("Query failed or returned null");
                error_log("SQL: " . $sql);
                if ($this->conn->error) {
                    error_log("MySQL Error: " . $this->conn->error);
                }
            } else {
                error_log("Found " . count($result) . " production plans");
            }
            
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
    
    // Lấy thống kê (CHỈ XƯỞNG 4)
    public function layThongKe() {
        try {
            // Chỉ đếm dây chuyền của xưởng 4
            $sql1 = "SELECT COUNT(*) as tongDC FROM daychuyen WHERE maXuong = 4";
            $result1 = $this->laydulieu($this->conn, $sql1);
            
            // Chỉ đếm phân bổ của xưởng 4
            $sql2 = "SELECT COUNT(*) as tongPhanBo 
                     FROM phanbodaychuyen pb
                     INNER JOIN daychuyen dc ON pb.maDC = dc.maDC
                     WHERE dc.maXuong = 4";
            $result2 = $this->laydulieu($this->conn, $sql2);
            
            // Lấy tên xưởng 4
            $sql3 = "SELECT tenXuong FROM xuong WHERE maXuong = 4 LIMIT 1";
            $result3 = $this->laydulieu($this->conn, $sql3);
            
            return array(
                'tongDayChuyen' => $result1 ? $result1[0]['tongDC'] : 0,
                'tongPhanBo' => $result2 ? $result2[0]['tongPhanBo'] : 0,
                'tenXuong' => $result3 ? $result3[0]['tenXuong'] : 'Xưởng 4'
            );
        } catch (Exception $e) {
            error_log("Lỗi layThongKe: " . $e->getMessage());
            return array('tongDayChuyen' => 0, 'tongPhanBo' => 0, 'tenXuong' => 'Xưởng 4');
        }
    }
    
    // Lấy danh sách phân bổ dây chuyền với thông tin chi tiết (CHỈ XƯỞNG 4)
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
                        sp.tenSP,
                        kh.ngayLap,
                        kh.hinhThuc
                    FROM phanbodaychuyen pb
                    INNER JOIN daychuyen dc ON pb.maDC = dc.maDC
                    INNER JOIN sanpham sp ON pb.maSP = sp.maSP
                    INNER JOIN kehoachsanxuat kh ON pb.maKHSX = kh.maKHSX
                    WHERE dc.maXuong = 4
                    ORDER BY pb.maPBDC DESC";
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
            $keyword = trim($keyword);
            if (empty($keyword)) {
                return $this->layDanhSachPhanBo();
            }
            
            $originalKeyword = $keyword;
            $keyword = $this->conn->real_escape_string($keyword);
            $numericKeyword = preg_replace('/[^0-9]/', '', $originalKeyword);
            
            // Kiểm tra nếu keyword bắt đầu bằng "DC" hoặc chỉ là số -> tìm theo maDC
            $isDCSearch = (stripos($originalKeyword, 'DC') === 0 || ctype_digit($originalKeyword));
            
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
                        sp.tenSP,
                        kh.ngayLap,
                        kh.hinhThuc
                    FROM phanbodaychuyen pb
                    INNER JOIN daychuyen dc ON pb.maDC = dc.maDC
                    INNER JOIN sanpham sp ON pb.maSP = sp.maSP
                    INNER JOIN kehoachsanxuat kh ON pb.maKHSX = kh.maKHSX
                    WHERE ";
            
            if ($isDCSearch && !empty($numericKeyword)) {
                // Chỉ tìm theo maDC
                $sql .= "pb.maDC = {$numericKeyword}";
            } else {
                // Tìm theo tên dây chuyền, sản phẩm, trạng thái
                $sql .= "(dc.tenDC LIKE '%{$keyword}%'
                         OR sp.tenSP LIKE '%{$keyword}%'
                         OR pb.trangThai LIKE '%{$keyword}%')";
            }
            
            $sql .= " ORDER BY pb.maPBDC DESC";
            
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi timKiemPhanBo: " . $e->getMessage());
            return array();
        }
    }
    
    // Thêm phân bổ dây chuyền mới
    public function themPhanBo($maDC, $maKHSX, $maSP, $soLuong, $ngayBatDau, $ngayKetThuc, $ghiChu = '') {
        // Validate input
        if (empty($maDC) || empty($maKHSX) || empty($maSP) || empty($soLuong)) {
            return false;
        }
        
        $maDC = intval($maDC);
        $maKHSX = intval($maKHSX);
        $maSP = intval($maSP);
        $soLuong = intval($soLuong);
        $ngayBatDau = $this->conn->real_escape_string($ngayBatDau);
        $ngayKetThuc = $this->conn->real_escape_string($ngayKetThuc);
        $ghiChu = $this->conn->real_escape_string($ghiChu);
        
        $sql = "INSERT INTO phanbodaychuyen (maDC, maKHSX, maSP, soLuong, ngayBatDau, ngayKetThuc, trangThai, ghiChu)
                VALUES ($maDC, $maKHSX, $maSP, $soLuong, '$ngayBatDau', '$ngayKetThuc', 'Chưa bắt đầu', '$ghiChu')";
        
        if ($this->conn->query($sql)) {
            return true;
        }
        
        return false;
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
