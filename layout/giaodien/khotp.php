<?php include_once('../../layout/header.php');?>
<!-- Sidebar -->
  <div class="sidebar">
    <div class="nav flex-column mt-2">

    <a href="#" class="active"><i class="bi bi-house-door me-2"></i>Trang chủ</a>

  <div class="nav-section">KHO THÀNH PHẨM</div>
  <a href="<?php echo isset(
$base_path) ? $base_path : ''; ?>/pages/nhapkho/lo_danh_sach.php"><i class="bi bi-box2-heart me-2"></i>Danh sách lô sản phẩm</a>
  <a href="<?php echo isset($base_path) ? $base_path : ''; ?>/pages/nhapkho/bao_cao_chat_luong.php"><i class="bi bi-file-earmark-bar-graph me-2"></i>Báo cáo chất lượng</a>
  <a href="<?php echo isset($base_path) ? $base_path : ''; ?>/pages/nhapkho/nhapkho.php"><i class="bi bi-arrow-down-right-square me-2"></i>Nhập kho thành phẩm</a>
  <a href="<?php echo isset($base_path) ? $base_path : ''; ?>/pages/nhapkho/xuatkho.php"><i class="bi bi-arrow-up-right-square me-2"></i>Xuất kho thành phẩm</a>
  <a href="<?php echo isset($base_path) ? $base_path : ''; ?>/pages/nhapkho/thongke_tonkho.php"><i class="bi bi-bar-chart me-2"></i>Thống kê tồn kho</a>
  <a href="<?php echo isset($base_path) ? $base_path : ''; ?>/pages/nhapkho/canh_bao.php"><i class="bi bi-exclamation-circle me-2"></i>Cảnh báo</a>

  </div>