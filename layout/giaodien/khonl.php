<?php include_once('../../layout/header.php');?>
<?php
session_start();
require_once("../../class/clslogin.php"); 
$p = new login();
    if (isset($_SESSION['id']) && isset($_SESSION['user']) && isset($_SESSION['pass']) && isset($_SESSION['phanquyen'])) {
        //$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen']);
        if (!$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen'])) {
            header("Location: ../dangnhap.php"); exit();
        }
    } else {
        header("Location: ../dangnhap.php");
        exit();
    }
$p->checkPagePermission('3');
?>
<!-- Sidebar -->
  <div class="sidebar">
    <div class="nav flex-column mt-2">

    <a href="#" class="active"><i class="bi bi-house-door me-2"></i>Trang chủ</a>

      <div class="nav-section">QUẢN LÝ KHO NGUYÊN LIỆU</div>
      <a href="#"><i class="bi bi-box-seam me-2"></i>Danh sách nguyên liệu</a>
      <a href="#"><i class="bi bi-box-seam me-2"></i>Danh sách phiếu yêu cầu nguyên liệu</a>
      <a href="#"><i class="bi bi-arrow-up-right-square me-2"></i>Phiếu xuất kho</a>
      <a href="#"><i class="bi bi-bar-chart me-2"></i>Thống kê tồn kho</a>
      <a href="#"><i class="bi bi-exclamation-triangle me-2"></i>Cảnh báo</a>
 
  </div>