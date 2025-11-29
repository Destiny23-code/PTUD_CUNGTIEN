<?php
include_once('../../layout/giaodien/khotp.php');
require_once("../../class/clsNhapKho.php");

if (!isset($_SESSION['hoTen'])) {
    header("Location: ../../pages/dangnhap/dangnhap.php");
    exit;
}

$nk = new nhapkho();
$dsLo = $nk->layLoNhapDuoc();
$tongSoLuong = array_sum(array_column($dsLo, 'soLuong'));

// Tạo mã phiếu
require_once("../../class/clsconnect.php");
$ketnoi_instance = new ketnoi();
$conn = $ketnoi_instance->connect();
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
        <h5 class="fw-bold text-primary mb-4">LẬP PHIẾU NHẬP KHO THÀNH PHẨM</h5>

        <?php if (empty($dsLo)): ?>
        <div class="alert alert-warning text-center py-5">
            Không có lô nào đủ điều kiện nhập kho!<br>
            Chỉ các lô đã được kiểm định <span class="badge bg-success">Đạt</span> và chưa nhập kho mới được hiển thị.
        </div>
        <?php else: ?>
        <div class="alert alert-success text-center py-3">
            Có <strong><?= count($dsLo) ?> lô</strong> đạt chất lượng – Tổng: <strong><?= number_format($tongSoLuong) ?>
                sản phẩm</strong>
        </div>
        <?php endif; ?>

        <!-- Bảng lô -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-hover text-center">
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
                    <tr>
                        <td class="fw-bold fs-5"><?= $row['maLo'] ?></td>
                        <td><?= $row['maSP'] ?></td>
                        <td><?= $row['tenSP'] ?></td>
                        <td><?= date('d/m/Y', strtotime($row['ngaySX'])) ?></td>
                        <td class="fw-bold text-primary fs-4"><?= number_format($row['soLuong']) ?></td>
                        <td><span class="badge bg-success">Đạt</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($dsLo)): ?>
        <!-- Thông tin phiếu -->
        <div class="card border-success mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">THÔNG TIN PHIẾU NHẬP</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Mã phiếu</label>
                        <div class="form-control fw-bold fs-4 text-success"><?= $maPhieuMoi ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tổng số lượng</label>
                        <div class="form-control fw-bold fs-4 text-primary"><?= number_format($tongSoLuong) ?> sản phẩm
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Người lập</label>
                        <div class="form-control fw-bold"><?= $_SESSION['hoTen'] ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày nhập kho</label>
                        <input type="date" id="ngayLap" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="text-end mt-4">
                    <button type="button" id="btnNhapKho" class="btn btn-success btn-lg px-5 shadow">
                        XÁC NHẬN NHẬP KHO TẤT CẢ
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Phiếu nhập kho (chỉ hiện khi thành công) -->
        <div id="phieuNhapKho" class="card shadow mt-4 d-none">
            <div class="card-body p-5" id="noiDungPhieu"></div>
        </div>
    </div>
</div>

<script>
document.getElementById("btnNhapKho")?.addEventListener("click", function() {
    if (!confirm("Xác nhận nhập kho tất cả lô?")) return;

    const formData = new FormData();
    document.querySelectorAll("tbody tr").forEach(row => {
        const maLo = row.cells[0].textContent.trim();
        formData.append('dsLo[]', maLo);
    });
    formData.append('ngayLap', document.getElementById("ngayLap").value);

    fetch('xuly_nhapkho.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById("noiDungPhieu").innerHTML = data.html;
                document.getElementById("phieuNhapKho").classList.remove('d-none');
                alert("Nhập kho thành công!");
                setTimeout(() => location.reload(), 3000);
            } else {
                alert("Lỗi: " + data.message);
            }
        })
        .catch(() => alert("Lỗi kết nối!"));
});
</script>

<?php include_once('../../layout/footer.php'); ?>