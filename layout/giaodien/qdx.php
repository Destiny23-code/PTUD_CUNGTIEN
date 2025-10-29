<?php include_once('../../layout/header.php'); ?>

<div class="sidebar">
  <div class="nav flex-column mt-2">
    <?php 
      // Base path (đường dẫn gốc)
      $link_trangchu    = $base_path . '/pages/trangchu/index.php';
      $link_ds_kehoach  = $base_path . '/pages/kehoach/danhsach.php';
      $link_phanbo_dc   = $base_path . '/pages/phanbo/daychuyen.php';
      $link_phancong_nc = $base_path . '/pages/phancong/nhancong.php';
      $link_ycnl        = $base_path . '/pages/yeucaunguyenlieu/pycnl.php';
      $link_yckd        = $base_path . '/pages/yeucaukiemdinh/pyckd.php';
      $link_thongke     = $base_path . '/pages/thongke/sanluong.php';
    ?>

    <!-- TRANG CHỦ -->
    <a href="<?php echo $link_trangchu; ?>" 
       class="<?php echo ($current_path == $link_trangchu) ? 'active' : ''; ?>">
       <i class="bi bi-house-door me-2"></i>Trang chủ
    </a>

    <!-- NHÓM MENU: QUẢN LÝ SẢN XUẤT -->
    <div class="nav-section mt-3">QUẢN LÝ SẢN XUẤT</div>

    <!-- Danh sách kế hoạch -->
    <a href="<?php echo $link_ds_kehoach; ?>" 
       class="<?php echo ($current_path == $link_ds_kehoach) ? 'active' : ''; ?>">
       <i class="bi bi-calendar-check me-2"></i>Danh sách kế hoạch
    </a>

    <!-- Phân bổ dây chuyền -->
    <a href="<?php echo $link_phanbo_dc; ?>" 
       class="<?php echo ($current_path == $link_phanbo_dc) ? 'active' : ''; ?>">
       <i class="bi bi-diagram-3 me-2"></i>Phân bổ dây chuyền
    </a>

    <!-- Phân công nhân công -->
    <a href="<?php echo $link_phancong_nc; ?>" 
       class="<?php echo ($current_path == $link_phancong_nc) ? 'active' : ''; ?>">
       <i class="bi bi-people me-2"></i>Phân công nhân công
    </a>

    <!-- Phiếu yêu cầu nguyên liệu -->
    <a href="<?php echo $link_ycnl; ?>" 
       class="<?php echo ($current_path == $link_ycnl) ? 'active' : ''; ?>">
       <i class="bi bi-file-earmark-text me-2"></i>Yêu cầu nguyên liệu
    </a>

    <!-- Phiếu yêu cầu kiểm định -->
    <a href="<?php echo $link_yckd; ?>" 
       class="<?php echo ($current_path == $link_yckd) ? 'active' : ''; ?>">
       <i class="bi bi-check2-square me-2"></i>Yêu cầu kiểm định
    </a>

    <!-- Thống kê sản lượng -->
    <a href="<?php echo $link_thongke; ?>" 
       class="<?php echo ($current_path == $link_thongke) ? 'active' : ''; ?>">
       <i class="bi bi-graph-up-arrow me-2"></i>Thống kê sản lượng
    </a>
  </div>
</div>
