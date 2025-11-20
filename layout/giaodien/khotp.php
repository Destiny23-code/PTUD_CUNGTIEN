<?php include_once('../../layout/header.php'); ?>
<!-- Sidebar -->
<div class="sidebar">
  <div class="nav flex-column mt-2">

    <a href="#" class="active"><i class="bi bi-house-door me-2"></i>Trang chủ</a>

    <div class="nav-section">KHO THÀNH PHẨM</div>
    <!-- <a href="#"><i class="bi bi-box2-heart me-2"></i>Danh sách lô sản phẩm</a> -->
    <?php
    // Chỉ thêm link cảnh báo theo đúng mẫu
    $link_canh_bao = $base_path . '/pages/canhbaokho/dslsp.php';
    $link_lo_sp = $base_path . '/pages/canhbaokho/dslsp_ori.php';
    ?>
    <a href="<?php echo $link_lo_sp; ?>"
      class="<?php echo ($current_path == $link_lo_sp) ? 'active' : ''; ?>">
      <i class="bi bi-box2-heart me-2"></i>Danh sách lô sản phẩm
    </a>
    <a href="#"><i class="bi bi-file-earmark-bar-graph me-2"></i>Báo cáo chất lượng</a>
    <a href="#"><i class="bi bi-arrow-down-right-square me-2"></i>Nhập kho thành phẩm</a>
    <a href="#"><i class="bi bi-arrow-up-right-square me-2"></i>Xuất kho thành phẩm</a>
    <a href="#"><i class="bi bi-bar-chart me-2"></i>Thống kê tồn kho</a>



    <a href="<?php echo $link_canh_bao; ?>"
      class="<?php echo ($current_path == $link_canh_bao) ? 'active' : ''; ?>">
      <i class="bi bi-exclamation-circle me-2"></i>Cảnh báo
    </a>

  </div>
</div>