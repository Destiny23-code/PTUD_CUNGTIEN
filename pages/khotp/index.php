<?php 
include_once('../../layout/giaodien/khotp.php'); 
// ← ĐÃ CÓ HEADER + SIDEBAR
?>

<div class="content">
    <div class="text-center mb-5">
        <h2 class="text-success fw-bold">
            KHO THÀNH PHẨM
        </h2>
        <h4>Xin chào <span class="text-primary"><?= htmlspecialchars($_SESSION['hoTen'] ?? 'Thủ kho') ?></span> -
            <?= date('d/m/Y') ?></h4>
    </div>

    <div class="row g-4 justify-content-center">
        <div class="col-md-4">
            <a href="xuatkho.php" class="btn btn-danger btn-lg w-100 p-5 shadow-lg rounded text-white">
                <i class="bi bi-truck fs-1"></i><br>
                <strong class="fs-4">XUẤT KHO</strong><br>
                <small>Giao hàng cho khách</small>
            </a>
        </div>

        <div class="col-md-4">
            <a href="nhapkho.php" class="btn btn-primary btn-lg w-100 p-5 shadow-lg rounded text-white">
                <i class="bi bi-box-arrow-in-down fs-1"></i><br>
                <strong class="fs-4">NHẬP KHO TP</strong><br>
                <small>Nhận sản phẩm từ xưởng</small>
            </a>
        </div>

        <div class="col-md-4">
            <a href="tktk.php" class="btn btn-info btn-lg w-100 p-5 shadow-lg rounded text-white">
                <i class="bi bi-bar-chart-line-fill fs-1"></i><br>
                <strong class="fs-4">THỐNG KÊ</strong><br>
                <small>Tồn kho, lịch sử nhập xuất</small>
            </a>
        </div>
    </div>



    <?php include_once('../../layout/footer.php'); ?>