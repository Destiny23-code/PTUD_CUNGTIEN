<?php include_once('../../layout/header.php');?>
<div class="sidebar">
    <div class="nav flex-column mt-2">

      <?php 
      // BƯỚC 3: Định nghĩa các link (để dễ so sánh)
      // (Bạn hãy tự điều chỉnh lại đường dẫn file cho chính xác)
      $link_trangchu = $base_path . '/pages/trangchu/index.php';
      $link_ds_kehoach = $base_path . '/pages/kehoachsanxuat/danhsach.php';
      $link_lap_kehoach = $base_path . '/pages/kehoachsanxuat/lapKH.php';
      $link_ds_donhang = $base_path . '/pages/donhang/index.php'; 
      ?>

      <a href="<?php echo $link_trangchu; ?>" 
         class="<?php echo ($current_path == $link_trangchu) ? 'active' : ''; ?>">
         <i class="bi bi-house-door me-2"></i>Trang chủ
      </a>

      <div class="nav-section">QUẢN LÝ KẾ HOẠCH</div>
      
      <a href="<?php echo $link_ds_kehoach; ?>" 
         class="<?php echo ($current_path == $link_ds_kehoach) ? 'active' : ''; ?>">
         <i class="bi bi-calendar-check me-2"></i>Danh sách kế hoạch
      </a>
      
      <a href="<?php echo $link_lap_kehoach; ?>" 
         class="<?php echo ($current_path == $link_lap_kehoach) ? 'active' : ''; ?>">
         <i class="bi bi-pencil-square me-2"></i>Lập & Điều chỉnh kế hoạch
      </a>

      <div class="nav-section">QUẢN LÝ ĐƠN HÀNG</div>
      
      <a href="<?php echo $link_ds_donhang; ?>" 
         class="<?php echo ($current_path == $link_ds_donhang) ? 'active' : ''; ?>">
         <i class="bi bi-bag-check me-2"></i>Danh sách đơn hàng
      </a>

    </div>
  </div>

