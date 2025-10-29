<?php
require_once("clsconnect.php"); 

class clsLapPYCKD extends ketnoi {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ✅ Lấy tất cả lô sản phẩm
    public function getLoSanPham() {
        $sql = "SELECT maLo, tenLo, ngaySanXuat, soLuong, trangThai 
                FROM losanpham
                ORDER BY maLo DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Lấy lô theo trạng thái (vd: Chờ kiểm định)
    public function getLoSanPhamByTrangThai($trangThai = 'Chờ kiểm định') {
        $sql = "SELECT maLo, tenLo, ngaySanXuat, soLuong, trangThai 
                FROM losanpham
                WHERE trangThai = :trangThai";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array(':trangThai' => $trangThai));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Thêm phiếu yêu cầu kiểm định mới
    public function insertPhieuYeuCauKiemDinh($data) {
        try {
            $this->conn->beginTransaction();

            $sql = "INSERT INTO phieuyeucaukiemdinh (ngayLap, nguoiLap, maLo, ghiChu, trangThai)
                    VALUES (CURDATE(), :nguoiLap, :maLo, :ghiChu, 'Chờ kiểm định')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($data);

            // Cập nhật trạng thái lô sang “Đang kiểm định”
            $update = $this->conn->prepare("UPDATE losanpham 
                                            SET trangThai = 'Đang kiểm định' 
                                            WHERE maLo = :maLo");
            $update->execute(array(':maLo' => $data[':maLo']));

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    // ✅ Lấy danh sách phiếu kiểm định đã lập
    public function getAllPhieuYCKD() {
        $sql = "SELECT p.maPYCKD, p.ngayLap, n.hoTen AS nguoiLap, l.tenLo, l.ngaySanXuat, p.trangThai
                FROM phieuyeucaukiemdinh p
                JOIN nhanvien n ON p.nguoiLap = n.maNV
                JOIN losanpham l ON p.maLo = l.maLo
                ORDER BY p.maPYCKD DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
