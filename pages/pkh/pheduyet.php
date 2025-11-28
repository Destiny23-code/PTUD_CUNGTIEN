<?php
require_once("../../class/clskehoachsx.php");
header('Content-Type: application/json; charset=utf-8');

$model = new KeHoachModel();

// Nếu yêu cầu xem chi tiết
if (isset($_GET['action']) && $_GET['action'] == 'xemChiTiet' && isset($_GET['maKHSX'])) {
    $maKHSX = $_GET['maKHSX'];
    $data = $model->getChiTietKeHoach($maKHSX);
    echo json_encode($data);
    exit;
}

// Nếu yêu cầu phê duyệt / từ chối
if (isset($_GET['action']) && $_GET['action'] == 'pheDuyet') {
    $maKHSX = isset($_POST['maKHSX']) ? $_POST['maKHSX'] : '';
    $hanhDong = isset($_POST['hanhDong']) ? $_POST['hanhDong'] : '';
    $lyDo = isset($_POST['lyDo']) ? $_POST['lyDo'] : '';

    if ($maKHSX != '' && $hanhDong != '') {
        $trangThai = ($hanhDong == 'duyet') ? 'Đã duyệt' : 'Từ chối';
        $ok = $model->capNhatTrangThaiKeHoach($maKHSX, $trangThai, $lyDo);

        echo json_encode(array('success' => $ok));
    } else {
        echo json_encode(array('success' => false, 'msg' => 'Thiếu dữ liệu.'));
    }
    exit;
}

// Nếu không có action nào → nạp giao diện danh sách
include("dskhsx.php");
?>
