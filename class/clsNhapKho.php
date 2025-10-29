<?php
require_once("clsconnect.php");

class nhapkho extends ketnoi {

    // Lấy danh sách các lô đạt QC (chỉ nhập các lô đạt QC)
    public function layLoNhapDuoc() {
        $link = $this->connect();
    // Lấy các lô đã có báo cáo chất lượng và được đánh giá 'Đạt' (hoặc có tiêu chí chứa 'đạt'/'ISO')
    $sql = "SELECT DISTINCT l.maLo, l.maSP, l.ngaySX, l.soLuong,
               sp.tenSP,
               COALESCE(pbcl.ketQuaBaoCao, pbcl.tieuChi) as trangThaiQC
        FROM losanpham l
        JOIN sanpham sp ON l.maSP = sp.maSP
        INNER JOIN phieubaocaochatluong pbcl ON pbcl.maLo = l.maLo
        WHERE (pbcl.ketQuaBaoCao IS NOT NULL AND pbcl.ketQuaBaoCao = 'Đạt')
           OR (pbcl.tieuChi IS NOT NULL AND (pbcl.tieuChi LIKE '%đạt%' OR pbcl.tieuChi LIKE '%Đạt%' OR pbcl.tieuChi LIKE '%ISO%'))
        ORDER BY l.ngaySX DESC";
        return $this->laydulieu($link, $sql);
    }
    // Tạo phiếu nhập kho (trả về id auto-increment maPNK)
    public function taoPhieuNhap($nguoiLap, $ngayNhap, $tongSL) {
        $link = $this->connect();
        $sql = "INSERT INTO phieunhapkho(ngayNhap, nguoiLap, tongSoLuongNhap)
                VALUES('$ngayNhap', '$nguoiLap', $tongSL)";
        if ($this->xuly($link, $sql)) {
            return $link->insert_id;
        }
        return false;
    }

    // Lưu chi tiết lô
    public function themChiTiet($maPNK, $maLo, $soLuong) {
        $link = $this->connect();
        $sql = "INSERT INTO chitiet_phieunhapkho(maPNK, maLo, soLuongNhap)
                VALUES('$maPNK', '$maLo', '$soLuong')";
        return $this->xuly($link, $sql);
    }

    // Cập nhật tồn kho sản phẩm
    public function capNhatTonKho($maLo, $soLuong) {
        $link = $this->connect();
        $sql = "UPDATE sanpham sp 
                JOIN losanpham l ON sp.maSP = l.maSP 
                SET sp.soLuongTon = sp.soLuongTon + $soLuong
                WHERE l.maLo = $maLo";
        return $this->xuly($link, $sql);
    }
}