<?php
require_once(__DIR__ . "/../../controller/cKeHoachSX.php");
$ctrl = new cKeHoachSX();

$action = $_GET['action'] ?? 'dsKeHoach';

switch($action){
    case 'dsKeHoach':
        $ctrl->hienThiDanhSach();
        break;
    case 'xemChiTiet':
        $ctrl->xemChiTiet();
        break;
    case 'pheDuyet':
        $ctrl->pheDuyet();
        break;
}
?>
