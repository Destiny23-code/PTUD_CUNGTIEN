<?php
require_once("clsconnect.php");

class KeHoachModel extends ketnoi {

    // Danh sách kế hoạch sản xuất
    public function getDanhSachKeHoach() {
        $link = $this->connect();
        $sql = "SELECT maKHSX, maDH, nguoiLap, ngayLap, hinhThuc, ngayBDDK, ngayKTDDK, trangThai, ghiChu, lyDoTuChoi
                FROM KEHOACHSANXUAT
                ORDER BY ngayLap DESC";
        $data = $this->laydulieu($link, $sql);
        $link->close();
        return $data;
    }

    // Chi tiết kế hoạch sản xuất
    public function getChiTietKeHoach($maKHSX) {
    $link = $this->connect();
    $maKHSX_safe = $link->real_escape_string($maKHSX);

    // ✅ Lấy thông tin kế hoạch sản xuất
    $sqlKH = "SELECT * FROM KEHOACHSANXUAT WHERE maKHSX = '$maKHSX_safe'";
    $info = $this->laydulieu($link, $sqlKH);
    $info = is_array($info) && count($info) > 0 ? $info[0] : [];

    // ✅ Lấy danh sách sản phẩm của đơn hàng tương ứng
    $sqlSP = "
        SELECT cdh.maSP, s.tenSP, s.donViTinh, cdh.soLuong
        FROM CHITIET_DONHANG cdh
        JOIN SANPHAM s ON cdh.maSP = s.maSP
        WHERE cdh.maDH = '{$info['maDH']}'
    ";
    $dsSanPham = $this->laydulieu($link, $sqlSP);

    // ✅ Lấy chi tiết nguyên liệu (kèm tên NL)
    $sqlNL = "
        SELECT nlct.maKHSX, nlct.maSP, nlct.maNL,
               nl.tenNL, nl.donViTinh,
               nlct.soLuong1SP, nlct.tongSLCan, 
               nlct.slTonTaiKho, nlct.slThieuHut, 
               nlct.phuongAnXuLy
        FROM KHSX_CHITIET_NGUYENLIEU nlct
        LEFT JOIN NGUYENLIEU nl ON nlct.maNL = nl.maNL
        WHERE nlct.maKHSX = '$maKHSX_safe'
    ";
    $dsNguyenLieu = $this->laydulieu($link, $sqlNL);

    $link->close();
    return [
        'thongtin' => $info,
        'sanpham' => $dsSanPham,
        'nguyenlieu' => $dsNguyenLieu
    ];
}

    // Duyệt hoặc từ chối kế hoạch
    public function capNhatTrangThaiKeHoach($maKHSX, $trangThai, $lyDo = null) {
    $link = $this->connect();
    $maKHSX_safe = $link->real_escape_string($maKHSX);
    $trangThai_safe = $link->real_escape_string($trangThai);
    $lyDo_safe = $lyDo ? $link->real_escape_string($lyDo) : null;

    // ✅ In ra câu SQL để dễ kiểm tra
    if ($trangThai_safe === 'Từ chối') {
        $sql = "UPDATE kehoachsanxuat 
                SET trangThai = '$trangThai_safe', lyDoTuChoi = '$lyDo_safe'
                WHERE maKHSX = '$maKHSX_safe'";
    } else {
        $sql = "UPDATE kehoachsanxuat 
                SET trangThai = '$trangThai_safe', lyDoTuChoi = NULL
                WHERE maKHSX = '$maKHSX_safe'";
    }

    // ✅ Thực thi và kiểm tra lỗi
    $result = $link->query($sql);

    if (!$result) {
        echo json_encode([
            'success' => false,
            'error_sql' => $link->error,
            'query' => $sql
        ]);
        $link->close();
        exit;
    }

    $link->close();
    echo json_encode(['success' => true]);
    exit;
}
}

class cKeHoachSX {
    private $model;

    public function __construct() {
        $this->model = new KeHoachModel();
    }

    public function getAll() {
        return $this->model->getDanhSachKeHoach();
    }

    public function hienThiDanhSach() {
        $ds = $this->model->getDanhSachKeHoach();
        include("pages/menuBGD.php");
    }

    public function xemChiTiet() {
    $maKHSX = $_GET['maKHSX'] ?? '';
    $data = $this->model->getChiTietKeHoach($maKHSX);
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}



    public function pheDuyet() {
    $maKHSX = $_POST['maKHSX'];
    $hanhDong = $_POST['hanhDong'];
    $nguoi = $_POST['nguoi'];
    $lyDo = isset($_POST['lyDo']) ? $_POST['lyDo'] : null;
    $trangThai = $hanhDong === 'duyet' ? 'Đã duyệt' : 'Từ chối';

    $kq = $this->model->capNhatTrangThaiKeHoach($maKHSX, $trangThai, $lyDo);

    // ✅ In ra JSON kết quả debug để xem SQL và lỗi
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($kq);
}

}
?>
