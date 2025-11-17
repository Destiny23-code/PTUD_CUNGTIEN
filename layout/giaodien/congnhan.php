<?php include_once('../../layout/header.php');?>
<?php
session_start();
require_once("../../class/clslogin.php"); 
$p = new login();
    if (isset($_SESSION['id']) && isset($_SESSION['user']) && isset($_SESSION['pass']) && isset($_SESSION['phanquyen'])) {
        if (!$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen'])) {
            header("Location: ../dangnhap.php"); exit();
        }
    } else {
        header("Location: ../dangnhap.php");
        exit();
    }
$p->checkPagePermission('7');
?>

<div class="sidebar">
    <div class="nav flex-column mt-2">
    <?php 
    $link_thongtin = $base_path . '/pages/congnhan/ttcn.php'; 
    $link_lichlamviec = $base_path . '/pages/congnhan/lichlamviec.php'; 
    ?>

    <a href="<?php echo $link_thongtin; ?>" 
        class="<?php echo $current_path == $link_thongtin ? 'active' : ''; ?>">
        <i class="bi bi-person-badge me-2"></i>Thông tin cá nhân
    </a>

    <a href="<?php echo $link_lichlamviec; ?>" 
        class="<?php echo $current_path == $link_lichlamviec ? 'active' : ''; ?>">
        <i class="bi bi-calendar3 me-2"></i>Lịch làm việc
    </a>
    </div>
</div>
