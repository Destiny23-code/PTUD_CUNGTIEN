<?php
require_once("clsconnect.php");
require_once(__DIR__ . "/../class/clsKeHoachModel.php");
class cKeHoachSX {
    private $model;

    public function __construct() {
        $this->model = new KeHoachModel();
    }

    // ✅ Lấy toàn bộ danh sách kế hoạch sản xuất
    public function getAll() {
        return $this->model->getDanhSachKeHoach();
    }

    // ✅ Hiển thị danh sách kế hoạch
    public function hienThiDanhSach() {
        $ds = $this->model->getDanhSachKeHoach();
        include("pages/menuBGD.php");
    }

    // ✅ Xem chi tiết kế hoạch (trả về JSON)
    public function xemChiTiet() {
        $maKHSX = $_GET['maKHSX'] ?? '';

        if (empty($maKHSX)) {
            http_response_code(400);
            echo json_encode(['error' => 'Thiếu mã kế hoạch sản xuất']);
            exit;
        }

        $data = $this->model->getChiTietKeHoach($maKHSX);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // ✅ Phê duyệt hoặc từ chối kế hoạch
    public function pheDuyet() {
        $maKHSX = $_POST['maKHSX'] ?? '';
        $hanhDong = $_POST['hanhDong'] ?? '';
        $nguoi = $_POST['nguoi'] ?? '';
        $lyDo = $_POST['lyDo'] ?? null;

        if (empty($maKHSX) || empty($hanhDong) || empty($nguoi)) {
            http_response_code(400);
            echo json_encode(['error' => 'Thiếu thông tin cần thiết']);
            exit;
        }

        $trangThai = ($hanhDong === 'duyet') ? 'Đã duyệt' : 'Từ chối';

        $kq = $this->model->capNhatTrangThaiKeHoach($maKHSX, $trangThai, $lyDo, $nguoi);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $kq ? true : false,
            'message' => $kq ? 'Cập nhật trạng thái thành công' : 'Cập nhật thất bại',
            'data' => [
                'maKHSX' => $maKHSX,
                'trangThai' => $trangThai,
                'lyDo' => $lyDo,
                'nguoi' => $nguoi
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
?>
