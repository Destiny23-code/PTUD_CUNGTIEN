<?php include_once('../../class/clsconnect.php'); ?>

<?php 
// BƯỚC 1: Lấy đường dẫn trang hiện tại (loại bỏ query string nếu có, vd: ?id=1)
$current_path = strtok($_SERVER["REQUEST_URI"], '?'); 

// BƯỚC 2: Định nghĩa đường dẫn gốc của dự án
$base_path = '/ptud_cungtien'; 
session_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hệ thống quản lý sản xuất</title>
  
  <link rel="stylesheet" href="<?php echo $base_path; ?>/layout/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

  <div class="header shadow-sm">
    <div class="d-flex align-items-center">
      <img src="<?php echo $base_path; ?>/layout/images/logo.png" alt="logo">
      <h6 class="m-0 fw-bold text-uppercase">HỆ THỐNG QUẢN LÝ SẢN XUẤT</h6>
    </div>
    <div class="d-flex align-items-center">
      <img src="https://www.w3schools.com/howto/img_avatar2.png" class="avatar">
      <span><b><?php echo htmlspecialchars($_SESSION['hoTen']); ?></b></span>
            <a href="/ptud_cungtien/pages/dangxuat.php" class="ms-3 btn btn-outline-danger btn-sm">Đăng xuất</a>
      <!--<span>User Account</span>-->
    </div>
  </div>