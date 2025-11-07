<?php 
include_once('../../layout/header.php'); 
// Lưu ý: Các biến $base_path và $current_path phải được định nghĩa trong file header.php hoặc file cấu hình
?>
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
$p->checkPagePermission('6');
?>

<div class="sidebar">
    <div class="nav flex-column mt-2">
        <?php 
        // 1. ĐỊNH NGHĨA CÁC ĐƯỜNG DẪN (Links)
        // Giả định các file trang chủ, kế hoạch và báo cáo nằm trong thư mục /pages/khsx/
        
        $link_trang_chu          = $base_path . '/pages/bgd/index.php';             // Trang chủ chung của hệ thống (hoặc /pages/khsx/index.php)
        $link_ds_khsx            = $base_path . '/pages/pkh/dskhsx.php';  // Danh sách kế hoạch sản xuất
        $link_baocao_thongke     = $base_path . '/pages/bgd/bctk.php';    // Báo cáo thống kê (Giả định tên file là bctk.php)
        
        // --- CÁC HÀNH ĐỘNG KHÁC CÓ THỂ THÊM VÀO ĐÂY ---
        ?>

        <a href="<?php echo $link_trang_chu; ?>" 
            class="<?php echo ($current_path == $link_trang_chu || $current_path == $base_path . '/') ? 'active' : ''; ?>">
            <i class="bi bi-house-door me-2"></i>Trang chủ
        </a>

        
        <div class="nav-section">QUẢN LÝ KẾ HOẠCH SẢN XUẤT</div>

        <a href="<?php echo $link_ds_khsx; ?>" 
            class="<?php echo ($current_path == $link_ds_khsx) ? 'active' : ''; ?>">
            <i class="bi bi-calendar-check me-2"></i>Danh sách kế hoạch sản xuất
        </a>

        
        <div class="nav-section">BÁO CÁO</div>

        <a href="<?php echo $link_baocao_thongke; ?>" 
            class="<?php echo ($current_path == $link_baocao_thongke) ? 'active' : ''; ?>">
            <i class="bi bi-bar-chart-fill me-2"></i>Báo cáo thống kê
        </a>
    </div>
</div>