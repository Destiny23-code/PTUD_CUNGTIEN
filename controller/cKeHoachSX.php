<?php
require_once(__DIR__ . "/../class/clsKeHoachModel.php");


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
        $nguoi = $_POST['nguoi']; // có thể lưu log sau này
        $lyDo = isset($_POST['lyDo']) ? $_POST['lyDo'] : null;
        $trangThai = $hanhDong === 'duyet' ? 'Đã duyệt' : 'Từ chối';

        $kq = $this->model->capNhatTrangThaiKeHoach($maKHSX, $trangThai, $lyDo);
        echo json_encode(['success' => $kq]);
    }
}
?>
