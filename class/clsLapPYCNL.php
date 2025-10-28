<?php
require_once("clsconnect.php"); 

class clsLapPYCNL extends ketnoi {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    // ✅ Lấy danh sách nguyên liệu đạt chuẩn
    public function getNguyenLieu() {
        $sql = "SELECT maNL, tenNL, moTa, dinhMuc, donViTinh, soLuongTon 
                FROM nguyenlieu WHERE trangThai = 'Đạt'";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Lấy danh sách kế hoạch sản xuất
    public function getKeHoachSanXuat() {
        $sql = "SELECT maKH, tenSP, soLuongCanSX FROM kehoachsanxuat";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Thêm phiếu + chi tiết phiếu
    public function insertPhieuYeuCau($nguoiLap, $details) {
        try {
            $this->conn->beginTransaction();

            // 1️⃣ Tạo phiếu mới
            $stmt = $this->conn->prepare("INSERT INTO phieuyeucaunguyenlieu (ngayLap, nguoiLap, trangThai)
                                          VALUES (CURDATE(), :nguoiLap, 'Chờ duyệt')");
            $stmt->execute([':nguoiLap' => $nguoiLap]);
            $maPYCNL = $this->conn->lastInsertId();

            // 2️⃣ Lặp chi tiết nguyên liệu
            $sqlDetail = "INSERT INTO chitietphieuyeucaunguyenlieu (maPYCNL, maKH, maNL, soLuongYeuCau)
                          VALUES (:maPYCNL, :maKH, :maNL, :soLuongYeuCau)";
            $stmtDetail = $this->conn->prepare($sqlDetail);

            foreach ($details as $item) {
                $stmtDetail->execute([
                    ':maPYCNL' => $maPYCNL,
                    ':maKH' => $item['maKH'],
                    ':maNL' => $item['maNL'],
                    ':soLuongYeuCau' => $item['soLuongYeuCau']
                ]);

                // 3️⃣ Trừ tồn kho tạm
                $upd = $this->conn->prepare("UPDATE nguyenlieu 
                                             SET soLuongTon = soLuongTon - :sl 
                                             WHERE maNL = :maNL AND soLuongTon >= :sl");
                $upd->execute([
                    ':sl' => $item['soLuongYeuCau'],
                    ':maNL' => $item['maNL']
                ]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    // ✅ Lấy danh sách phiếu yêu cầu
    public function getAllPhieuYeuCau() {
        $sql = "SELECT p.maPYCNL, p.ngayLap, u.hoTen AS nguoiLap, p.trangThai 
                FROM phieuyeucaunguyenlieu p
                JOIN nhanvien u ON p.nguoiLap = u.maNV
                ORDER BY p.maPYCNL DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Lấy chi tiết phiếu yêu cầu nguyên liệu
    public function getChiTietPhieu($maPYCNL) {
        $sql = "SELECT c.maCTPYCNL, n.tenNL, k.tenSP, c.soLuongYeuCau
                FROM chitietphieuyeucaunguyenlieu c
                JOIN nguyenlieu n ON c.maNL = n.maNL
                JOIN kehoachsanxuat k ON c.maKH = k.maKH
                WHERE c.maPYCNL = :ma";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':ma' => $maPYCNL]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>