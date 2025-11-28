<?php
// FIX ĐƯỜNG DẪN GỐC - BẮT BUỘC PHẢI CÓ
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = substr($script_name, 0, strpos($script_name, '/pages') !== false ? strpos($script_name, '/pages') : strlen($script_name));
if ($base_path === false || $base_path === '') $base_path = '/PTUD_CUNGTIEN';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hệ thống quản lý sản xuất</title>

    <!-- CSS - ĐÃ SỬA ĐƯỜNG DẪN + CACHE BUSTING -->
    <link rel="stylesheet" href="<?php echo $base_path; ?>/layout/css/style.css?v=<?php echo time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Phần header còn lại của em giữ nguyên -->

    <body>

        <div class="header shadow-sm">
            <div class="d-flex align-items-center">
                <img src="<?php echo $base_path; ?>/layout/images/logo.png" alt="logo" style="height:50px;">
                <h6 class="m-0 fw-bold text-uppercase ms-3">HỆ THỐNG QUẢN LÝ SẢN XUẤT</h6>
            </div>
            <div class="d-flex align-items-center">
                <img src="<?php echo $base_path; ?>/layout/images/user.png" class="avatar rounded-circle me-2"
                    style="width:40px; height:40px;">
                <span class="me-3"><b><?php echo htmlspecialchars($_SESSION['hoTen'] ?? 'Guest'); ?></b></span>
                <a href="<?php echo $base_path; ?>/pages/dangxuat.php" class="btn btn-outline-danger btn-sm">
                    Đăng xuất
                </a>
            </div>
        </div>

        <div style="margin-top: 80px;"></div> <!-- Khoảng trống cho header fixed -->