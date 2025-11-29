<?php
class nhapkho {

    public function layLoNhapDuoc() {
        require_once("clsconnect.php");
        
        // Cách gọi ổn định 100% – không cần sửa clsconnect.php
        $ketnoi_instance = new ketnoi();
        $conn = $ketnoi_instance->connect();

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
                ORDER BY l.maLo DESC";

        $result = $conn->query($sql);
        $ds = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ds[] = $row;
            }
        }
        $conn->close();
        return $ds;
    }
}
?>