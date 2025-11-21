<?php 
require_once("../../class/session_init.php");
require_once("../../class/clsNhapKho.php");
include_once('../../layout/giaodien/khotp.php');

if (!isset($_SESSION['hoTen'])) {
    header("Location: ../../pages/dangnhap/dangnhap.php");
    exit;
}

$nk = new nhapkho();
$dsLo = $nk->layLoNhapDuoc();

// Tính tổng số lượng
$tongSoLuong = 0;
foreach ($dsLo as $row) {
    $tongSoLuong += $row['soLuong'];
}

// Tạo mã phiếu tự động theo thứ tự
$conn = (new ketnoi())->connect();
$ngayHomNay = date('Ymd');
$sqlMax = "SELECT maPNK FROM phieunhapkho WHERE maPNK LIKE 'PNK$ngayHomNay%' ORDER BY maPNK DESC LIMIT 1";
$maPhieuMoi = "PNK$ngayHomNay-001";

if ($result = $conn->query($sqlMax)) {
    if ($row = $result->fetch_assoc()) {
        $soHienTai = (int)substr($row['maPNK'], -3);
        $soMoi = $soHienTai + 1;
        $maPhieuMoi = "PNK$ngayHomNay-" . str_pad($soMoi, 3, '0', STR_PAD_LEFT);
    }
}
$conn->close();
?>

<div class="content">
    <div class="card shadow-sm p-4">
        <h5 class="fw-bold text-primary mb-4">
            <i class="bi bi-arrow-down-square-fill me-2"></i>LẬP PHIẾU NHẬP KHO THÀNH PHẨM
        </h5>

        <?php if (empty($dsLo)): ?>
        <div class="alert alert-warning text-center py-5">
            <i class="bi bi-exclamation-triangle-fill fs-1 d-block mb-3 text-warning"></i>
            <strong>Không có lô nào đủ điều kiện nhập kho!</strong><br>
            Chỉ các lô đã được kiểm định <span class="badge bg-success">Đạt</span> và chưa nhập kho mới được hiển thị.
        </div>
        <?php else: ?>
        <div class="alert alert-success text-center py-3">
            <i class="bi bi-check-circle-fill me-2"></i>
            Có <strong><?= count($dsLo) ?> sản phẩm</strong> thuộc các lô đạt chất lượng.<br>
            Tất cả sẽ được nhập kho một lần duy nhất.
        </div>
        <?php endif; ?>

        <form id="frmNhapKho" action="xuly_nhapkho.php" method="POST">
            <!-- BẢNG LÔ GIỮ NGUYÊN Ở TRÊN -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>Mã lô</th>
                            <th>Mã SP</th>
                            <th>Tên SP</th>
                            <th>Ngày SX</th>
                            <th>Số lượng</th>
                            <th>QC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dsLo as $row): ?>
                        <tr class="table-light">
                            <td class="fw-bold fs-5"><?= $row['maLo'] ?></td>
                            <td><?= $row['maSP'] ?></td>
                            <td><?= $row['tenSP'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['ngaySX'])) ?></td>
                            <td class="fw-bold text-primary fs-4"><?= number_format($row['soLuong']) ?></td>
                            <td><span class="badge bg-success">Đạt</span></td>
                            <input type="hidden" name="dsLo[]" value="<?= $row['maLo'] ?>">
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PHIẾU NHẬP KHO - ĐÃ DI CHUYỂN NGƯỜI LẬP + NGÀY LẬP VÀO ĐÂY -->
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>PHIẾU NHẬP KHO (SẴN SÀNG XÁC NHẬN)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mã phiếu (tự động)</label>
                            <input type="text" id="maPhieu" name="maPhieu" class="form-control fw-bold fs-4"
                                value="<?= $maPhieuMoi ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tổng số lượng nhập</label>
                            <input type="text" class="form-control text-primary fw-bold fs-4"
                                value="<?= number_format($tongSoLuong) ?> sản phẩm" readonly>
                        </div>
                    </div>

                    <!-- DI CHUYỂN VÀO ĐÂY -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Người lập</label>
                            <input type="text" class="form-control fw-bold" value="<?= $_SESSION['hoTen'] ?? '' ?>"
                                readonly>
                            <input type="hidden" name="nguoiLap" value="<?= $_SESSION['maNV'] ?? '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày nhập kho</label>
                            <input type="date" id="ngayLap" name="ngayLap" class="form-control"
                                value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <h6 class="fw-bold text-success mb-3">Các lô được nhập kho toàn bộ</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-success">
                                <tr class="text-center">
                                    <th>Mã lô</th>
                                    <th>Mã SP</th>
                                    <th>Tên SP</th>
                                    <th>Ngày SX</th>
                                    <th>Số lượng nhập</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($dsLo as $row): ?>
                                <tr class="text-center">
                                    <td class="fw-bold fs-5"><?= $row['maLo'] ?></td>
                                    <td><?= $row['maSP'] ?></td>
                                    <td><?= $row['tenSP'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['ngaySX'])) ?></td>
                                    <td class="text-primary fw-bold fs-4"><?= number_format($row['soLuong']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="bi bi-check2-all me-2"></i>Xác nhận nhập kho tất cả
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include_once('../../layout/footer.php'); ?>