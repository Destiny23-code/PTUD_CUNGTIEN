<?php include_once('../../layout/header.php'); ?>
<!-- Sidebar -->
<div class="sidebar">
  <div class="nav flex-column mt-2">
    <?php 
      // Định nghĩa các link cho sidebar QC (tương tự code mẫu)
      $link_ds_pyckd     = $base_path . '/pages/qc/dspyckd.php';  // Danh sách phiếu yêu cầu kiểm định
      $link_ds_lo_sp     = $base_path . '/pages/qc/dslsp.php';     // Danh sách lô sản phẩm (giả sử file là dslo.php, bạn có thể đổi tên)
      $link_lap_bc_cl    = $base_path . '/pages/qc/lbccl.php'; // Lập báo cáo chất lượng (giả sử file là lapbaocao.php, bạn có thể đổi tên)
    ?>

    <!-- NHÓM MENU: QUẢN LÝ CHẤT LƯỢNG -->
    <div class="nav-section">QUẢN LÝ CHẤT LƯỢNG</div>

    <!-- Danh sách phiếu yêu cầu kiểm định -->
    <a href="<?php echo $link_ds_pyckd; ?>" 
       class="<?php echo ($current_path == $link_ds_pyckd) ? 'active' : ''; ?>">
       <i class="bi bi-clipboard-check me-2"></i>Danh sách phiếu yêu cầu kiểm định
    </a>

    <!-- Danh sách lô sản phẩm -->
    <a href="<?php echo $link_ds_lo_sp; ?>" 
       class="<?php echo ($current_path == $link_ds_lo_sp) ? 'active' : ''; ?>">
       <i class="bi bi-box-seam me-2"></i>Danh sách lô sản phẩm
    </a>

    <!-- Lập báo cáo chất lượng -->
    <a href="<?php echo $link_lap_bc_cl; ?>" 
       class="<?php echo ($current_path == $link_lap_bc_cl) ? 'active' : ''; ?>">
       <i class="bi bi-file-earmark-bar-graph me-2"></i>Lập báo cáo chất lượng
    </a>
  </div>
</div>
