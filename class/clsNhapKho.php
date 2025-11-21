<?php
class nhapkho {

    public function layLoNhapDuoc() {
        $conn = (new ketnoi())->connect();
        if (!$conn) return [];

        // FIX CHÍNH: Dùng EXISTS để kiểm tra lô có ít nhất 1 báo cáo "Đạt" và chưa nhập kho
        $sql = "SELECT 
                    l.maLo,
                    l.maSP,
                    sp.tenSP,
                    l.ngaySX,
                    l.soLuong
                FROM losanpham l
                JOIN sanpham sp ON l.maSP = sp.maSP
                WHERE EXISTS (
                    SELECT 1 FROM phieubaocaochatluong bc 
                    WHERE bc.maLo = l.maLo AND bc.ketQuaBaoCao = 'Đạt'
                )
                AND NOT EXISTS (
                    SELECT 1 FROM chitiet_phieunhapkho ct 
                    WHERE ct.maLo = l.maLo
                )
                ORDER BY l.maLo DESC, l.maSP ASC";

        $result = $conn->query($sql);
        $ds = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['trangThaiQC'] = 'Đạt QC';
                $ds[] = $row;
            }
        }
        $conn->close();
        return $ds;
    }
}
?>