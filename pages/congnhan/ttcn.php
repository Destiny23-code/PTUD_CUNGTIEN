<?php include_once("../../layout/giaodien/congnhan.php"); 
include_once("../../class/clsCongNhan.php"); 
session_start();

$cn = new Congnhan();
$tt = $cn->getTTCN($_SESSION['maNV']); 


?>
<!-- Content -->
  <div class="content">
    <h5 class="fw-bold text-primary mb-4">
      <i class="bi bi-person-badge me-2"></i>Thông tin cá nhân
    </h5>

    <div class="card shadow-sm" style="border-radius: 12px;">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-3 text-center">
            <?php
            // Hiển thị avatar khác nhau dựa trên giới tính
            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['hoTen']) . 
                         '&size=180&bold=true&rounded=true&format=svg';
            
            // Màu nền khác nhau cho nam/nữ
            if ($tt['gioiTinh'] == 'Nam') {
                $avatarUrl .= '&background=4A90E2&color=fff'; // Xanh dương cho nam
            } else {
                $avatarUrl .= '&background=E91E63&color=fff'; // Hồng cho nữ
            }
            ?>
            <img src="<?php echo $avatarUrl; ?>" class="avatar shadow" style="width: 180px;height: 180px;border-radius: 50%;margin-bottom: 10px;">
            <h6 class="mt-2"><b><?php echo htmlspecialchars($_SESSION['hoTen']); ?></b></h6>
            <span class="badge bg-primary"><?php echo $tt['tenLoai']?></span>
          </div>

          <div class="col-md-9">
            <div class="row mb-2">
              <div class="col-md-6"><span class="fw-bold">Mã nhân viên:</span> <?php echo htmlspecialchars($_SESSION['maNV']); ?></div>
              <div class="col-md-6"><span class="fw-bold">Giới tính:</span> <?php echo $tt['gioiTinh']?></div>
            </div>
            <div class="row mb-2">
              <div class="col-md-6"><span class="fw-bold">Ngày vào làm:</span> <?php echo $tt['ngayVaoLam']?></div>
              <div class="col-md-6"><span class="fw-bold">Số điện thoại:</span> <?php echo $tt['sDT']?></div>
            </div>
            <div class="row mb-2">
              <div class="col-md-6"><span class="fw-bold">Địa chỉ:</span> <?php echo $tt['diaChi']?></div>
            </div>

            <hr>

            <div class="row mb-2">
              <div class="col-md-6"><span class="fw-bold text-primary"><i class="bi bi-diagram-3 me-1"></i>Dây chuyền:</span> <?php echo $tt['tenDC']?></div>
              <div class="col-md-6"><span class="fw-bold text-primary"><i class="bi bi-building me-1"></i>Xưởng:</span> <?php echo $tt['tenXuong']?></div>
            </div>

            <div class="mt-3">
              <button class="btn btn-outline-primary btn-sm"><i class="bi bi-key me-1"></i> Cập nhật mật khẩu</button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>


<?php include_once("../../layout/footer.php"); ?>
