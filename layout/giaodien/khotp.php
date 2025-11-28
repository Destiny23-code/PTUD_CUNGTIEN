<?php
session_start();
require_once("../../class/clslogin.php");
$p = new login();

// Kiểm tra đăng nhập
if (!isset($_SESSION['id']) || !isset($_SESSION['user']) || !isset($_SESSION['pass']) || !isset($_SESSION['phanquyen'])) {
    header("Location: ../../pages/dangnhap.php");
    exit();
}
if (!$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen'])) {
    header("Location: ../../pages/dangnhap.php");
    exit();
}

// Chỉ cho phép thủ kho (phanquyen = 4)
$p->checkPagePermission('4');

include_once('../../layout/header.php');

// Tính base_path và current_path để active menu đúng
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = substr($script_name, 0, strrpos($script_name, '/pages'));
$current_path = strtok($_SERVER["REQUEST_URI"], '?');
?>

<div class="sidebar">
    <div class="nav flex-column mt-2">

        <?php
        $link_trangchu     = $base_path . '/pages/khotp/index.php';
        $link_bccl         = $base_path . '/pages/khotp/bccl.php';
        $link_nhapkho      = $base_path . '/pages/khotp/nhapkho.php';
        $link_xuatkho      = $base_path . '/pages/khotp/xuatkho.php';
        $link_thongke      = $base_path . '/pages/khotp/thongke.php';
        $link_canhbao      = $base_path . '/pages/khotp/canhbao.php';
        $link_dslsp        = $base_path . '/pages/khotp/dslsp.php';
        ?>

        <a href="<?= $link_trangchu ?>"
            class="nav-link <?= ($current_path == parse_url($link_trangchu, PHP_URL_PATH)) ? 'active' : '' ?>">
            Trang chủ kho
        </a>

        <div class="nav-section fw-bold text-primary mt-3">KHO THÀNH PHẨM</div>

        <a href="<?= $link_bccl ?>"
            class="nav-link <?= ($current_path == parse_url($link_bccl, PHP_URL_PATH)) ? 'active' : '' ?>">
            Báo cáo chất lượng
        </a>

        <a href="<?= $link_nhapkho ?>"
            class="nav-link <?= ($current_path == parse_url($link_nhapkho, PHP_URL_PATH)) ? 'active' : '' ?>">
            Nhập kho thành phẩm
        </a>

        <a href="<?= $link_xuatkho ?>"
            class="nav-link <?= ($current_path == parse_url($link_xuatkho, PHP_URL_PATH)) ? 'active' : '' ?>">
            Xuất kho thành phẩm
        </a>

        <a href="<?= $link_thongke ?>"
            class="nav-link <?= ($current_path == parse_url($link_thongke, PHP_URL_PATH)) ? 'active' : '' ?>">
            Thống kê tồn kho
        </a>

        <a href="<?= $link_canhbao ?>"
            class="nav-link <?= ($current_path == parse_url($link_canhbao, PHP_URL_PATH)) ? 'active' : '' ?>">
            Cảnh báo tồn kho
        </a>

        <a href="<?= $link_dslsp ?>"
            class="nav-link <?= ($current_path == parse_url($link_dslsp, PHP_URL_PATH)) ? 'active' : '' ?>">
            Danh sách sản phẩm
        </a>

    </div>
</div>