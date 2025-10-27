<?php include_once('../../layout/header.php');?>
<div class="sidebar">
    <div class="nav flex-column mt-2">
    <?php 
        // BƯỚC 3: Định nghĩa các link (để dễ so sánh)
        // (Bạn hãy tự điều chỉnh lại đường dẫn file cho chính xác)
        $link_trangchu = $base_path . '/pages/trangchu/index.php';
        $link_ycnl = $base_path . '/pages/pycnl.php';
        $link_yckd = $base_path . '/pages/pyckd.php';
        ?>
        <a href="<?php echo $link_trangchu; ?>" 
         class="<?php echo ($current_path == $link_trangchu) ? 'active' : ''; ?>">
         <i class="bi bi-house-door me-2"></i>Trang chủ
      </a>

      <div class="nav-section">QUẢN LÝ SẢN XUẤT</div>

      <a href="<?php echo $link_ds_kehoach; ?>" 
         class="<?php echo ($current_path == $link_ds_kehoach) ? 'active' : ''; ?>">
         <i class="bi bi-calendar-check me-2"></i>Danh sách kế hoạch
      </a>
    
      <a href="#"><i class="bi bi-diagram-3 me-2"></i>Phân bổ dây chuyền</a>
      <a href="#"><i class="bi bi-people me-2"></i>Phân công nhân công</a>

      <a href="<?php echo $link_ycnl; ?>"
        class="<?php echo ($current_path == $link_ycnl) ? 'active' : ''; ?>">
        <i class="bi bi-file-earmark-text me-2"></i>Yêu cầu nguyên liệu</a>

      <a href="<?php echo $link_yckd; ?>"
        class="<?php echo ($current_path == $link_yckd) ? 'active' : ''; ?>">
        <i class="bi bi-check2-square me-2"></i>Yêu cầu kiểm định</a>
      <a href="#"><i class="bi bi-graph-up-arrow me-2"></i>Thống kê sản lượng</a>

  </div>
    </div>
  </div>