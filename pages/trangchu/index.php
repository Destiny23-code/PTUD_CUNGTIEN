<?php
session_start();
require_once("../../class/clslogin.php"); 
$p = new login();
    if (isset($_SESSION['id']) && isset($_SESSION['user']) && isset($_SESSION['pass']) && isset($_SESSION['phanquyen'])) {
        //$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen']);
        if (!$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen'])) {
            header("Location: ../dangnhap/dangnhap.php"); exit();
        }
    } else {
        header("Location: ../dangnhap/dangnhap.php");
        exit();
    }
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6>Chào mừng <?php echo htmlspecialchars($_SESSION['hoTen']); ?> đến với Hệ thống!</h6>

    </div>
</div>