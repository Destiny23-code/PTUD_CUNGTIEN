<?php 
include_once('../../layout/header.php'); 
// Giả định $base_path (đường dẫn gốc) và $current_path (đường dẫn hiện tại) đã được định nghĩa
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
$p->checkPagePermission('4');

?>

<div class="sidebar">
    <div class="nav flex-column mt-2">
        <?php 
        // 1. ĐỊNH NGHĨA CÁC ĐƯỜNG DẪN (Links) cho Kho Thành Phẩm (KTP)
        // Giả sử các file nằm trong thư mục /pages/kho_tp/
        
        $link_trang_chu             = $base_path . '/pages/kho_tp/index.php';             // Trang chủ chung của hệ thống
        $link_ds_lo_sp              = $base_path . '/pages/kho_tp/dslsp.php';    // Danh sách lô sản phẩm
        $link_bc_chat_luong         = $base_path . '/pages/kho_tp/bccl.php';     // Báo cáo chất lượng
        $link_nhap_kho              = $base_path . '/pages/kho_tp/nhapkho.php';  // Nhập kho thành phẩm
        $link_xuat_kho              = $base_path . '/pages/kho_tp/xuatkho.php';  // Xuất kho thành phẩm
        $link_thong_ke_ton_kho      = $base_path . '/pages/kho_tp/tktk.php';     // Thống kê tồn kho
        $link_canh_bao              = $base_path . '/pages/kho_tp/canhbao.php';  // Cảnh báo

        // Lưu ý: Thay đổi tên file (.php) nếu cần thiết để khớp với cấu trúc thư mục thực tế của bạn
        ?>

        <a href="<?php echo $link_trang_chu; ?>" 
            class="<?php echo ($current_path == $link_trang_chu || $current_path == $base_path . '/') ? 'active' : ''; ?>">
            <i class="bi bi-house-door me-2"></i>Trang chủ
        </a>

        
        <div class="nav-section">KHO THÀNH PHẨM</div>

        <a href="<?php echo $link_ds_lo_sp; ?>" 
            class="<?php echo ($current_path == $link_ds_lo_sp) ? 'active' : ''; ?>">
            <i class="bi bi-box2-heart me-2"></i>Danh sách lô sản phẩm
        </a>

        <a href="<?php echo $link_bc_chat_luong; ?>" 
            class="<?php echo ($current_path == $link_bc_chat_luong) ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-bar-graph me-2"></i>Báo cáo chất lượng
        </a>

        <a href="<?php echo $link_nhap_kho; ?>" 
            class="<?php echo ($current_path == $link_nhap_kho) ? 'active' : ''; ?>">
            <i class="bi bi-arrow-down-right-square me-2"></i>Nhập kho thành phẩm
        </a>

        <a href="<?php echo $link_xuat_kho; ?>" 
            class="<?php echo ($current_path == $link_xuat_kho) ? 'active' : ''; ?>">
            <i class="bi bi-arrow-up-right-square me-2"></i>Xuất kho thành phẩm
        </a>

        <a href="<?php echo $link_thong_ke_ton_kho; ?>" 
            class="<?php echo ($current_path == $link_thong_ke_ton_kho) ? 'active' : ''; ?>">
            <i class="bi bi-bar-chart me-2"></i>Thống kê tồn kho
        </a>

        <a href="<?php echo $link_canh_bao; ?>" 
            class="<?php echo ($current_path == $link_canh_bao) ? 'active' : ''; ?>">
            <i class="bi bi-exclamation-circle me-2"></i>Cảnh báo
        </a>
    </div>
</div>