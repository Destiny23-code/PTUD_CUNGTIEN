<?php
include_once('../../layout/giaodien/khotp.php');
require_once("../../class/clsNhapKho.php");

if (!isset($_SESSION['hoTen'])) {
    header("Location: ../../pages/dangnhap/dangnhap.php");
    exit;
}

$nk = new nhapkho();
$dsLo = $nk->layLoNhapDuoc();

// Tính tổng số lượng
$tongSoLuong = 0;
foreach ($dsLo as $lo) {
    $tongSoLuong += (int)$lo['soLuong'];
}

// Tạo mã phiếu tự động
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
            Có <strong><?php echo count($dsLo); ?> lô</strong> đạt chất lượng – Tổng:
            <strong><?php echo number_format($tongSoLuong); ?> sản phẩm</strong>
        </div>
        <?php endif; ?>

        <!-- Bảng danh sách lô -->
        <?php if (!empty($dsLo)): ?>
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
                        <td class="fw-bold fs-5"><?php echo $row['maLo']; ?></td>
                        <td><?php echo $row['maSP']; ?></td>
                        <td><?php echo htmlspecialchars($row['tenSP']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['ngaySX'])); ?></td>
                        <td class="fw-bold text-primary fs-4"><?php echo number_format($row['soLuong']); ?></td>
                        <td><span class="badge bg-success">Đạt</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Thông tin phiếu -->
        <div class="card border-success mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">THÔNG TIN PHIẾU NHẬP</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Mã phiếu</label>
                        <div class="form-control fw-bold fs-4 text-success"><?php echo $maPhieuMoi; ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tổng số lượng</label>
                        <div class="form-control fw-bold fs-4 text-primary"><?php echo number_format($tongSoLuong); ?>
                            sản phẩm</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Người lập</label>
                        <div class="form-control fw-bold"><?php echo $_SESSION['hoTen']; ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày nhập kho</label>
                        <input type="date" id="ngayLap" class="form-control" value="<?php echo date('Y-m-d'); ?>"
                            required>
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
    </div>
</div>

<!-- MODAL PHIẾU NHẬP KHO – CÓ NÚT X + IN -->
<div class="modal fade" id="modalPhieuNhap" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 border-success border-3">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title fw-bold">PHIẾU NHẬP KHO THÀNH PHẨM</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="noiDungPhieuNhap">
                <!-- Nội dung sẽ được JS chèn vào -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary btn-lg" onclick="window.print()">
                    In Phiếu Nhập Kho
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('btnNhapKho').onclick = function() {
    if (!confirm('Xác nhận nhập kho tất cả các lô đạt chất lượng?')) return;

    var dsLo = [];
    document.querySelectorAll('tbody tr').forEach(function(row) {
        var maLo = row.cells[0].textContent.trim();
        dsLo.push(maLo);
    });

    var formData = new FormData();
    for (var i = 0; i < dsLo.length; i++) {
        formData.append('dsLo[]', dsLo[i]);
    }
    formData.append('ngayLap', document.getElementById('ngayLap').value);

    fetch('xuly_nhapkho.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) {
            return r.json();
        })
        .then(function(data) {
            if (data.status === 'success') {
                document.getElementById('noiDungPhieuNhap').innerHTML = data.html;
                var modal = new bootstrap.Modal(document.getElementById('modalPhieuNhap'));
                modal.show();

                // Khi đóng modal → reload trang
                document.getElementById('modalPhieuNhap').addEventListener('hidden.bs.modal', function() {
                    location.reload();
                });
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(function() {
            alert('Lỗi kết nối đến server!');
        });
};
</script>

<?php include_once('../../layout/footer.php'); ?>