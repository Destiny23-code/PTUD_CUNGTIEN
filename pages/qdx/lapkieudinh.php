<?php
if (!isset($_SESSION)) session_start();
require_once("../../class/clslogin.php");
require_once("../../class/clsLapPYCKD.php");

$p = new login();
$pyckd = new clsLapPYCKD();

// Kiểm tra quyền Quản đốc (phanquyen = 2)
if (!$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen']) || $_SESSION['phanquyen'] != 2) {
    header("Location: ../dangnhap.php");
    exit();
}

// Lấy mã lô từ URL
$maLo = isset($_GET['maLo']) ? $_GET['maLo'] : '';

// Lấy thông tin chi tiết của lô và sản phẩm để hiển thị tiêu chí
$conn = $pyckd->connect();
$sql_info = "SELECT l.maLo, s.tenSP, s.tieuChiKiemDinh, l.soLuong 
             FROM losanpham l 
             JOIN sanpham s ON l.maSP = s.maSP 
             WHERE l.maLo = '" . $conn->real_escape_string($maLo) . "' LIMIT 1";
$res = $pyckd->laydulieu($conn, $sql_info);
$info = !empty($res) ? $res[0] : null;

$msg = "";
if (isset($_POST['btnLapPhieu'])) {
    $nguoiLap = $_SESSION['id']; // ID của nhân viên đang đăng nhập
    $ghiChu = $_POST['ghiChu'];
    
    if ($pyckd->insertPhieuYeuCauKiemDinh($nguoiLap, $maLo, $ghiChu)) {
        echo "<script>alert('Lập phiếu yêu cầu kiểm định thành công!'); window.location.href='dslosp.php';</script>";
    } else {
        $msg = "Có lỗi xảy ra trong quá trình lập phiếu.";
    }
}

include_once('../../layout/giaodien/qdx.php');
?>

<div class="content">
    <div class="container-fluid">
        <h3 class="mb-4 text-primary"><i class="bi bi-file-earmark-medical me-2"></i>LẬP PHIẾU YÊU CẦU KIỂM ĐỊNH</h3>
        
        <?php if ($info): ?>
        <div class="card shadow border-0">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Mã Lô:</label>
                            <input type="text" class="form-control bg-light" value="<?php echo $info['maLo']; ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Sản Phẩm:</label>
                            <input type="text" class="form-control bg-light" value="<?php echo $info['tenSP']; ?>" readonly>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="fw-bold text-danger">Tiêu Chí Kiểm Định (Từ danh mục sản phẩm):</label>
                            <div class="p-3 bg-light border rounded">
                                <?php echo nl2br(htmlspecialchars($info['tieuChiKiemDinh'])); ?>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="fw-bold">Ghi chú / Yêu cầu thêm:</label>
                            <textarea name="ghiChu" class="form-control" rows="4" placeholder="Nhập các yêu cầu cụ thể cho nhân viên QC..."></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" name="btnLapPhieu" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-1"></i> Xác Nhận Lập Phiếu
                        </button>
                        <a href="dslosp.php" class="btn btn-secondary px-4">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-danger">Không tìm thấy thông tin lô sản phẩm!</div>
        <?php endif; ?>
    </div>
</div>

<?php include_once("../../layout/footer.php"); ?>