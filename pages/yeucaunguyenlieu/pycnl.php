<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once("../../class/clsconnect.php");
require_once("../../class/clsLapPYCNL.php");

// Kiểm tra đăng nhập
if (!isset($_SESSION['maND'])) {
    header("Location: ../dangnhap/dangnhap.php");
    exit();
}

// Kết nối CSDL
$ketnoi = new ketnoi();
$conn = $ketnoi->connect();
$conn->set_charset("utf8");

$cls = new clsLapPYCNL($conn);
$msg = "";

// Lấy danh sách kế hoạch sản xuất và xưởng
$listKH = $ketnoi->laydulieu($conn, "SELECT maKHSX, maDH FROM kehoachsanxuat");
$listXuong = $ketnoi->laydulieu($conn, "SELECT maXuong, tenXuong FROM xuong");

// Xử lý khi nhấn “Lập phiếu”
if (isset($_POST['btnLapPhieu'])) {
    $nguoiLap = $_SESSION['maND']; // Lấy mã người dùng đăng nhập
    $maXuong = $_POST['maXuong'];
    $maKHSX = $_POST['maKHSX'];

    $sql = "INSERT INTO phieuyeucaunguyenlieu (ngayLap, nguoiLap, maXuong, maKHSX, trangThai)
            VALUES (CURDATE(), '$nguoiLap', '$maXuong', '$maKHSX', 'Chờ duyệt')";
    
    $ok = $conn->query($sql);

    if ($ok) {
        $new_id = $conn->insert_id;
        $msg = "✅ Đã lập phiếu yêu cầu nguyên liệu thành công! (Mã Phiếu: $new_id)";
    } else {
        $msg = "❌ Lỗi khi lập phiếu: " . $conn->error;
    }
}

// --- Gọi layout header ---
include_once("../../layout/header.php");

// --- Gọi layout sidebar ---
include_once("../../layout/sidebar.php");
?>

<div class="main-content">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fas fa-clipboard-list me-2"></i> LẬP PHIẾU YÊU CẦU NGUYÊN LIỆU
        </div>
        <div class="card-body">
            <?php if ($msg != ""): ?>
                <div class="alert alert-info"><?php echo $msg; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="maKHSX" class="form-label fw-bold">1. Kế hoạch Sản xuất:</label>
                        <select name="maKHSX" id="maKHSX" class="form-select" required>
                            <option value="">-- Chọn Kế hoạch --</option>
                            <?php 
                            if ($listKH && $listKH->num_rows > 0) {
                                while ($row = $listKH->fetch_assoc()) {
                                    echo "<option value='{$row['maKHSX']}'>KHSX: {$row['maKHSX']} (ĐH: {$row['maDH']})</option>";
                                }
                            } else {
                                echo "<option value=''>Không có Kế hoạch Sản xuất nào</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="maXuong" class="form-label fw-bold">2. Xưởng yêu cầu:</label>
                        <select name="maXuong" id="maXuong" class="form-select" required>
                            <option value="">-- Chọn Xưởng --</option>
                            <?php 
                            if ($listXuong && $listXuong->num_rows > 0) {
                                while ($row = $listXuong->fetch_assoc()) {
                                    echo "<option value='{$row['maXuong']}'>{$row['tenXuong']}</option>";
                                }
                            } else {
                                echo "<option value=''>Không có Xưởng nào</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 text-center mt-4">
                        <button type="submit" name="btnLapPhieu" class="btn btn-success btn-lg">
                            <i class="fas fa-file-signature me-2"></i> LẬP PHIẾU YÊU CẦU NGUYÊN LIỆU
                        </button>
                        <p class="text-muted mt-2">
                            <i class="bi bi-info-circle-fill"></i> Phiếu sẽ được lập với ngày hiện tại và trạng thái “Chờ duyệt”.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once("../../layout/footer.php"); ?>
