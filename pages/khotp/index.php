<<<<<<< HEAD:pages/kho_tp/index.php
<?php 
require_once('../../class/session_init.php');

// Chỉ cho phép thủ kho thành phẩm vào (maLoai = 4)
if (!isset($_SESSION['maLoai']) || $_SESSION['maLoai'] != 4) {
    header("Location: ../../pages/dangnhap.php");
    exit;
}
include_once('../../layout/header.php');
include_once('../../layout/giaodien/khotp.php');   // giữ giao diện cũ anh đang dùng
?>

<div class="content">
    <div class="text-center mb-5">
        <h2 class="text-success fw-bold">
            <i class="bi bi-building-fill"></i> KHO THÀNH PHẨM
        </h2>
        <h4>Xin chào <span class="text-primary"><?= $_SESSION['hoTen'] ?? 'Thủ kho' ?></span> - Ngày
            <?= date('d/m/Y') ?></h4>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Xuất kho -->
        <div class="col-md-4">
            <a href="xuatkho.php" class="btn btn-danger btn-lg w-100 p-5 shadow-lg rounded">
                <i class="bi bi-truck fs-1"></i><br>
                <strong class="fs-4">XUẤT KHO</strong><br>
                <small>Giao hàng cho khách</small>
            </a>
        </div>

        <!-- Nhập kho thành phẩm -->
        <div class="col-md-4">
            <a href="nhapkho.php" class="btn btn-primary btn-lg w-100 p-5 shadow-lg rounded">
                <i class="bi bi-box-arrow-in-down fs-1"></i><br>
                <strong class="fs-4">NHẬP KHO TP</strong><br>
                <small>Nhận sản phẩm từ xưởng</small>
            </a>
        </div>

        <!-- Thống kê kho -->
        <div class="col-md-4">
            <a href="thongke.php" class="btn btn-info btn-lg w-100 p-5 shadow-lg rounded text-white">
                <i class="bi bi-bar-chart-line-fill fs-1"></i><br>
                <strong class="fs-4">THỐNG KÊ</strong><br>
                <small>Tồn kho, lịch sử nhập xuất</small>
            </a>
        </div>
    </div>

    <div class="mt-5 text-center">
        <div class="alert alert-light border">
            <i class="bi bi-info-circle"></i>
            Hôm nay tồn kho:
            <strong class="text-primary">350ml: 1.640</strong> |
            <strong class="text-success">500ml: 9.600</strong> |
            <strong class="text-warning">1.5L: 5.700</strong>
        </div>
    </div>
</div>

<?php include_once('../../layout/footer.php'); ?>
=======

<?php 
include_once('../../layout/giaodien/khotp.php'); ?>


<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6>Chào mừng <b><?php echo htmlspecialchars($_SESSION['hoTen']); ?></b> đến với Hệ thống!</h6>
</div></div>

>>>>>>> a040c0c6144f3aaee9a773d3eb09b6647c8a29e6:pages/khotp/index.php
