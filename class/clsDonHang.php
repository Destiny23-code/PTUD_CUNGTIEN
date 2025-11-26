<?php
// File: ../../class/clsKhachHang.php
include_once('clsconnect.php'); // Phải có để kế thừa kết nối CSDL

class DonHang extends ketnoi {
    
    /**
     * Tra cứu thông tin khách hàng dựa trên Số điện thoại (SDT).
     * @param string $sdt Số điện thoại cần tra cứu.
     * @return array | null Trả về mảng dữ liệu khách hàng nếu tìm thấy, ngược lại là null.
     */
    public function getKhachHangByPhone($sdt) {
        $conn = $this->connect(); // Mở kết nối
        
        // Làm sạch dữ liệu trước khi truy vấn
        $sdt_safe = $conn->real_escape_string($sdt);
        
        // Giả định tên bảng là 'khachhang'
        $sql = "SELECT maKH, tenKH, diaChi FROM khachhang WHERE sdt = '{$sdt_safe}' LIMIT 1";
        
        $data = $this->laydulieu($conn, $sql);
        
        $conn->close(); // Đóng kết nối
        
        // Trả về hàng đầu tiên nếu tìm thấy, ngược lại là mảng rỗng
        return !empty($data) ? $data[0] : null;
    }

    public function getDSSanPham(){
        $link = $this->connect();
        $sql = "select maSP, tenSP from sanpham";
        $data = $this->laydulieu($link, $sql);
        return $data;
    }
    public function thucthisql($sql){
        $link = $this->connect();
        if($link->query($sql)){
            return 1;
        }
        else{
            return 0;
        }
    }
}
?>