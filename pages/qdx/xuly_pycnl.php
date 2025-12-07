<?php
header('Content-Type: text/html; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra session và quyền truy cập
if (!isset($_SESSION)) {
    session_start();
}

require_once("../../class/clslogin.php");
require_once("../../class/clsLapPYCNL.php");

$p = new login();

// Kiểm tra session
$session_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
$session_user = isset($_SESSION['user']) ? $_SESSION['user'] : '';
$session_pass = isset($_SESSION['pass']) ? $_SESSION['pass'] : '';
$session_phanquyen = isset($_SESSION['phanquyen']) ? $_SESSION['phanquyen'] : 0;

if (!$p->confirmlogin($session_id, $session_user, $session_pass, $session_phanquyen) || $session_phanquyen != 2) {
    header("Location: ../dangnhap.php");
    exit();
}

$pycnl = new LapPYCNL();

// Xử lý hủy phiếu
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $maPYCNL = intval($_GET['id']);
    
    if ($maPYCNL > 0) {
        $result = $pycnl->updateTrangThaiPhieu($maPYCNL, 'Đã hủy');
        
        if ($result) {
            echo "<script>alert('Đã hủy phiếu thành công!'); window.location.href='dspycnl.php';</script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra khi hủy phiếu!'); window.location.href='dspycnl.php';</script>";
        }
    } else {
        echo "<script>alert('Mã phiếu không hợp lệ!'); window.location.href='dspycnl.php';</script>";
    }
    exit();
}

// Xử lý duyệt phiếu (dành cho quản lý kho)
if (isset($_GET['action']) && $_GET['action'] == 'approve' && isset($_GET['id'])) {
    $maPYCNL = intval($_GET['id']);
    
    if ($maPYCNL > 0) {
        $result = $pycnl->updateTrangThaiPhieu($maPYCNL, 'Đã duyệt');
        
        if ($result) {
            echo "<script>alert('Đã duyệt phiếu thành công!'); window.location.href='dspycnl.php';</script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra khi duyệt phiếu!'); window.location.href='dspycnl.php';</script>";
        }
    } else {
        echo "<script>alert('Mã phiếu không hợp lệ!'); window.location.href='dspycnl.php';</script>";
    }
    exit();
}

// Xử lý cấp nguyên liệu (dành cho quản lý kho)
if (isset($_GET['action']) && $_GET['action'] == 'supply' && isset($_GET['id'])) {
    $maPYCNL = intval($_GET['id']);
    
    if ($maPYCNL > 0) {
        $result = $pycnl->updateTrangThaiPhieu($maPYCNL, 'Đã cấp');
        
        if ($result) {
            echo "<script>alert('Đã cấp nguyên liệu thành công!'); window.location.href='dspycnl.php';</script>";
        } else {
            echo "<script>alert('Có lỗi xảy ra khi cấp nguyên liệu!'); window.location.href='dspycnl.php';</script>";
        }
    } else {
        echo "<script>alert('Mã phiếu không hợp lệ!'); window.location.href='dspycnl.php';</script>";
    }
    exit();
}

// Nếu không có action nào, quay về trang danh sách
header("Location: dspycnl.php");
exit();
?>
