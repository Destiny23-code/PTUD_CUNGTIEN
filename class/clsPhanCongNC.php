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
    
    // Thêm phân công mới với tích hợp lịch làm việc
    public function themPhanCong($maDC, $maNV, $ngayLamViec, $gioBatDau, $gioKetThuc, $ghiChu = '', $maXuongQuanDoc = null) {
        try {
            // Bắt đầu transaction
            $this->conn->autocommit(false);
            
            // Kiểm tra dây chuyền có thuộc xưởng của quản đốc không
            if ($maXuongQuanDoc !== null) {
                $sqlCheckXuong = "SELECT maXuong FROM daychuyen WHERE maDC = " . intval($maDC);
                $resultXuong = $this->laydulieu($this->conn, $sqlCheckXuong);
                
                if (!$resultXuong || $resultXuong[0]['maXuong'] != $maXuongQuanDoc) {
                    $this->conn->rollback();
                    $this->conn->autocommit(true);
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
                    $this->conn->rollback();
                    $this->conn->autocommit(true);
                    return "Nhân viên này không thuộc xưởng của dây chuyền đang chọn!";
                }
            } else {
                $this->conn->rollback();
                $this->conn->autocommit(true);
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
                $this->conn->rollback();
                $this->conn->autocommit(true);
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
            
            if (!$this->conn->query($sql)) {
                $this->conn->rollback();
                $this->conn->autocommit(true);
                return "Lỗi tạo phân công: " . $this->conn->error;
            }
            
            // Lấy ID của phân công vừa tạo
            $maPC = $this->conn->insert_id;
            
            // Xác định ca làm việc phù hợp
            $thongTinCa = $this->xacDinhCaLam($gioBatDau, $gioKetThuc);
            $maCa = $thongTinCa ? $thongTinCa['maCa'] : 1; // Mặc định ca sáng nếu không tìm thấy
            
            // Kiểm tra tăng ca
            $thongTinTangCa = $this->kiemTraTangCa($gioBatDau, $gioKetThuc, $thongTinCa);
            $trangThai = $thongTinTangCa['laTangCa'] ? 'Tăng ca' : 'Nghỉ phép'; // Sử dụng enum có sẵn
            
            $gioTangCaBD = isset($thongTinTangCa['gioTangCaBatDau']) ? $thongTinTangCa['gioTangCaBatDau'] : null;
            $gioTangCaKT = isset($thongTinTangCa['gioTangCaKetThuc']) ? $thongTinTangCa['gioTangCaKetThuc'] : null;
            
            // Tạo lịch làm việc
            $ketQuaLichLamViec = $this->taoLichLamViec($maNV, $ngayLamViec, $maCa, $trangThai, $maPC, $gioTangCaBD, $gioTangCaKT, $ghiChu);
            
            if ($ketQuaLichLamViec !== true) {
                $this->conn->rollback();
                $this->conn->autocommit(true);
                return "Lỗi tạo lịch làm việc: " . $ketQuaLichLamViec;
            }
            
            // Commit transaction
            $this->conn->commit();
            $this->conn->autocommit(true);
            
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            $this->conn->autocommit(true);
            error_log("Lỗi themPhanCong: " . $e->getMessage());
            return "Lỗi: " . $e->getMessage();
        }
    }
    
    // Lấy thông tin ca làm việc từ bảng calam
    public function layThongTinCaLam() {
        try {
            $sql = "SELECT maCa, tenCa, gioBatDau, gioKetThuc FROM calam ORDER BY maCa";
            $result = $this->laydulieu($this->conn, $sql);
            return $result ? $result : array();
        } catch (Exception $e) {
            error_log("Lỗi layThongTinCaLam: " . $e->getMessage());
            return array();
        }
    }
    
    // Xác định ca làm việc dựa trên giờ bắt đầu và kết thúc
    private function xacDinhCaLam($gioBatDau, $gioKetThuc) {
        try {
            $danhSachCa = $this->layThongTinCaLam();
            $caPhiHop = null;
            $doPhuHopCaoNhat = 0;
            
            foreach ($danhSachCa as $ca) {
                $gioBDCa = $ca['gioBatDau'];
                $gioKTCa = $ca['gioKetThuc'];
                
                // Tính độ phù hợp (overlap) giữa thời gian phân công và ca làm việc
                $batDauMax = max($gioBatDau, $gioBDCa);
                $ketThucMin = min($gioKetThuc, $gioKTCa);
                
                if ($batDauMax < $ketThucMin) {
                    // Có overlap - tính phần trăm overlap
                    $thoiGianOverlap = strtotime($ketThucMin) - strtotime($batDauMax);
                    $thoiGianCa = strtotime($gioKTCa) - strtotime($gioBDCa);
                    $doPhuHop = $thoiGianOverlap / $thoiGianCa;
                    
                    if ($doPhuHop > $doPhuHopCaoNhat) {
                        $doPhuHopCaoNhat = $doPhuHop;
                        $caPhiHop = $ca;
                    }
                }
            }
            
            // Nếu không tìm thấy ca phù hợp, chọn ca gần nhất
            if (!$caPhiHop && !empty($danhSachCa)) {
                $khoangCachNhoNhat = PHP_INT_MAX;
                foreach ($danhSachCa as $ca) {
                    $khoangCach = abs(strtotime($gioBatDau) - strtotime($ca['gioBatDau']));
                    if ($khoangCach < $khoangCachNhoNhat) {
                        $khoangCachNhoNhat = $khoangCach;
                        $caPhiHop = $ca;
                    }
                }
            }
            
            return $caPhiHop;
        } catch (Exception $e) {
            error_log("Lỗi xacDinhCaLam: " . $e->getMessage());
            return null;
        }
    }
    
    // Kiểm tra xem có phải tăng ca không
    private function kiemTraTangCa($gioBatDau, $gioKetThuc, $thongTinCa) {
        try {
            if (!$thongTinCa) {
                return array(
                    'laTangCa' => true,
                    'gioTangCaBatDau' => $gioBatDau,
                    'gioTangCaKetThuc' => $gioKetThuc
                );
            }
            
            $gioBDCa = $thongTinCa['gioBatDau'];
            $gioKTCa = $thongTinCa['gioKetThuc'];
            
            // Kiểm tra xem có vượt quá giờ ca không
            $vuotGioBatDau = strtotime($gioBatDau) < strtotime($gioBDCa);
            $vuotGioKetThuc = strtotime($gioKetThuc) > strtotime($gioKTCa);
            
            if ($vuotGioBatDau || $vuotGioKetThuc) {
                // Tính giờ tăng ca
                $gioTangCaBD = $vuotGioBatDau ? $gioBatDau : $gioKTCa;
                $gioTangCaKT = $vuotGioKetThuc ? $gioKetThuc : $gioBDCa;
                
                return array(
                    'laTangCa' => true,
                    'gioTangCaBatDau' => $gioTangCaBD,
                    'gioTangCaKetThuc' => $gioTangCaKT
                );
            }
            
            return array('laTangCa' => false);
        } catch (Exception $e) {
            error_log("Lỗi kiemTraTangCa: " . $e->getMessage());
            return array('laTangCa' => false);
        }
    }
    
    // Tạo lịch làm việc với xử lý lỗi toàn diện
    private function taoLichLamViec($maNV, $ngay, $maCa, $trangThai, $maPC, $gioTangCaBatDau = null, $gioTangCaKetThuc = null, $ghiChu = '') {
        try {
            // Kiểm tra ràng buộc khóa ngoại trước khi insert
            $sqlCheckNV = "SELECT COUNT(*) as total FROM nhanvien WHERE maNV = " . intval($maNV);
            $resultNV = $this->laydulieu($this->conn, $sqlCheckNV);
            if (!$resultNV || $resultNV[0]['total'] == 0) {
                return "Nhân viên không tồn tại trong hệ thống (maNV: {$maNV})";
            }
            
            $sqlCheckCa = "SELECT COUNT(*) as total FROM calam WHERE maCa = " . intval($maCa);
            $resultCa = $this->laydulieu($this->conn, $sqlCheckCa);
            if (!$resultCa || $resultCa[0]['total'] == 0) {
                return "Ca làm việc không tồn tại trong hệ thống (maCa: {$maCa})";
            }
            
            // Kiểm tra trạng thái hợp lệ
            $trangThaiHopLe = array('Tăng ca', 'Nghỉ phép');
            if (!in_array($trangThai, $trangThaiHopLe)) {
                return "Trạng thái không hợp lệ. Chỉ chấp nhận: " . implode(', ', $trangThaiHopLe);
            }
            
            $sql = "INSERT INTO lichlamviec 
                    (maNV, ngay, maCa, trangThai, gioTangCaBatDau, gioTangCaKetThuc, ghiChu, maPC)
                    VALUES (
                        " . intval($maNV) . ",
                        '{$ngay}',
                        " . intval($maCa) . ",
                        '{$trangThai}',
                        " . ($gioTangCaBatDau ? "'{$gioTangCaBatDau}'" : "NULL") . ",
                        " . ($gioTangCaKetThuc ? "'{$gioTangCaKetThuc}'" : "NULL") . ",
                        '" . $this->conn->real_escape_string($ghiChu) . "',
                        " . intval($maPC) . "
                    )";
            
            if ($this->conn->query($sql)) {
                return true;
            } else {
                $errorCode = $this->conn->errno;
                $errorMsg = $this->conn->error;
                
                // Xử lý các lỗi cụ thể
                switch ($errorCode) {
                    case 1452: // Foreign key constraint fails
                        if (strpos($errorMsg, 'fk_lichlamviec_nv') !== false) {
                            return "Nhân viên không tồn tại hoặc đã bị xóa khỏi hệ thống";
                        } elseif (strpos($errorMsg, 'fk_lichlamviec_ca') !== false) {
                            return "Ca làm việc không tồn tại trong hệ thống";
                        } elseif (strpos($errorMsg, 'lichlamviec_ibfk_1') !== false) {
                            return "Phân công không tồn tại hoặc đã bị xóa";
                        } else {
                            return "Lỗi ràng buộc dữ liệu: " . $errorMsg;
                        }
                    case 1062: // Duplicate entry
                        return "Lịch làm việc đã tồn tại cho nhân viên này trong ngày đã chọn";
                    case 1406: // Data too long
                        return "Dữ liệu nhập vào quá dài, vui lòng kiểm tra lại";
                    case 1264: // Out of range value
                        return "Giá trị không hợp lệ, vui lòng kiểm tra lại thông tin";
                    default:
                        return "Lỗi cơ sở dữ liệu: " . $errorMsg . " (Mã lỗi: {$errorCode})";
                }
            }
        } catch (Exception $e) {
            error_log("Lỗi taoLichLamViec: " . $e->getMessage());
            return "Lỗi hệ thống: " . $e->getMessage();
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
