<?php
session_start();
include_once('../../layout/header.php');
require_once('../../class/clsLapPYCKD.php');
require_once('../../class/clsconnect.php');

// ✅ Kiểm tra quyền đăng nhập
if (!isset($_SESSION['vai_tro']) || $_SESSION['vai_tro'] != 'quản đốc') {
    echo "<script>alert('Bạn không có quyền truy cập chức năng này!');window.location.href='../../pages/trangchu/index.php';</script>";
    exit();
}

// ✅ Kết nối CSDL
$ketnoi = new ketnoi();
$conn = $ketnoi->connect();
$pyckd = new clsLapPYCKD($conn);

// ✅ Lấy danh sách lô sản phẩm chờ kiểm định
$dsLo = $pyckd->getLoSanPhamByTrangThai('Chờ kiểm định');

// ✅ Nếu nhấn nút “Lập phiếu”
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnLapPhieu'])) {
    $maLo = $_POST['maLo'];
    $ghiChu = isset($_POST['ghiChu']) ? $_POST['ghiChu'] : '';
    $nguoiLap = $_SESSION['maNV']; // lấy từ session người dùng hiện tại

    $data = array(
        ':nguoiLap' => $nguoiLap,
        ':maLo' => $maLo,
        ':ghiChu' => $ghiChu
    );

    $kq = $pyckd->insertPhieuYeuCauKiemDinh($data);
    if ($kq) {
        echo "<script>alert('Đã lập phiếu yêu cầu kiểm định thành công!');window.location.reload();</script>";
    } else {
        echo "<script>alert('Lập phiếu thất bại!');</script>";
    }
}

// ✅ Lấy danh sách phiếu kiểm định đã lập
$dsPhieu = $pyckd->getAllPhieuYCKD();
?>

<div class="main-content p-4">
    <h3 class="text-center text-primary mb-4">PHIẾU YÊU CẦU KIỂM ĐỊNH</h3>

    <!-- FORM LẬP PHIẾU -->
    <form method="POST" class="border p-3 rounded bg-light shadow-sm mb-4" style="max-width:700px;margin:auto;">
        <div class="mb-3">
            <label class="form-label fw-bold">Chọn lô sản phẩm:</label>
            <select name="maLo" class="form-select" required>
                <option value="">-- Chọn lô chờ kiểm định --</option>
                <?php foreach ($dsLo as $lo): ?>
                    <option value="<?php echo htmlspecialchars($lo['maLo']); ?>">
                        <?php echo htmlspecialchars($lo['tenLo']) . " - Ngày SX: " . htmlspecialchars($lo['ngaySanXuat']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Ghi chú:</label>
            <textarea name="ghiChu" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" name="btnLapPhieu" class="btn btn-primary">
            <i class="bi bi-file-earmark-plus"></i> Lập phiếu kiểm định
        </button>
    </form>

    <!-- DANH SÁCH PHIẾU KIỂM ĐỊNH -->
    <h5 class="mt-5 mb-3 text-success">Danh sách phiếu kiểm định đã lập</h5>
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th>Mã phiếu</th>
                <th>Ngày lập</th>
                <th>Người lập</th>
                <th>Lô sản phẩm</th>
                <th>Ngày sản xuất</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($dsPhieu) > 0): ?>
                <?php foreach ($dsPhieu as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['maPYCKD']); ?></td>
                        <td><?php echo htmlspecialchars($row['ngayLap']); ?></td>
                        <td><?php echo htmlspecialchars($row['nguoiLap']); ?></td>
                        <td><?php echo htmlspecialchars($row['tenLo']); ?></td>
                        <td><?php echo htmlspecialchars($row['ngaySanXuat']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($row['trangThai']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">Chưa có phiếu kiểm định nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include_once('../../layout/footer.php'); ?>
