<?php
include_once('../../layout/giaodien/khotp.php');
require_once('../../class/clsconnect.php');

if (!isset($_SESSION['hoTen'])) {
    header('Location: ../../pages/dangnhap/dangnhap.php');
    exit;
}

$ketnoi_instance = new ketnoi();
$conn = $ketnoi_instance->connect();

// Chỉ hiển thị các mục đủ tồn kho để xuất
$sql = "
SELECT 
    dh.maDH, dh.ngayGiaoDuKien, kh.tenKH,
    ct.maSP, sp.tenSP, ct.soLuong AS slCanXuat, ct.donGia,
    sp.soLuongTon
FROM donhang dh
JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
JOIN khachhang kh ON dh.maKH = kh.maKH
JOIN sanpham sp ON ct.maSP = sp.maSP
WHERE dh.trangThai NOT IN ('Hoàn thành','Đã hủy')
  AND ct.soLuong <= sp.soLuongTon
ORDER BY dh.ngayGiaoDuKien ASC, dh.maDH
";
$rs = $conn->query($sql);
?>

<style>
.content {
    margin-left: 250px;
    margin-top: 80px;
    padding: 20px;
}

body {
    background-color: #f4f6f9;
    font-size: 14px;
}

.card {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

.table thead {
    background: linear-gradient(90deg, #007bff, #0056b3);
    color: white;
}

.donhang-row {
    cursor: pointer;
    transition: all 0.2s;
}

.donhang-row.table-active {
    background-color: #bbdefb !important;
    font-weight: 600;
}

.btn-success {
    border-radius: 50px;
    padding: 12px 40px;
    font-weight: bold;
    font-size: 16px;
}
</style>

<div class="content">
    <div class="card shadow-sm p-4">
        <h5 class="fw-bold text-primary mb-4">
            Xuất kho - Chỉ xuất khi đủ 100% tồn kho
        </h5>

        <?php if ($rs->num_rows == 0): ?>
        <div class="alert alert-warning text-center py-5">
            Chưa có đơn hàng nào đủ hàng để xuất kho.
        </div>
        <?php else: ?>

        <form id="frmXuatKho">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="5%">Chọn</th>
                            <th width="8%">Mã ĐH</th>
                            <th width="22%">Khách hàng</th>
                            <th width="25%">Sản phẩm</th>
                            <th width="10%" class="text-center">SL cần</th>
                            <th width="10%" class="text-center">Tồn kho</th>
                            <th width="12%" class="text-center">Ngày giao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $rs->fetch_assoc()): ?>
                        <tr class="donhang-row">
                            <td class="text-center">
                                <input type="checkbox" class="donhang-checkbox"
                                    value="<?php echo $row['maDH']; ?>_<?php echo $row['maSP']; ?>_<?php echo $row['slCanXuat']; ?>_<?php echo $row['donGia']; ?>">
                            </td>
                            <td><strong>#<?php echo str_pad($row['maDH'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['tenKH']); ?></td>
                            <td><?php echo htmlspecialchars($row['tenSP']); ?></td>
                            <td class="text-end fw-bold text-danger"><?php echo number_format($row['slCanXuat']); ?>
                            </td>
                            <td class="text-end text-success fw-bold"><?php echo number_format($row['soLuongTon']); ?>
                            </td>
                            <td class="text-center"><?php echo date('d/m/Y', strtotime($row['ngayGiaoDuKien'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-end mt-4">
                <button type="button" class="btn btn-success shadow-lg" data-bs-toggle="modal"
                    data-bs-target="#modalXacNhanXuat">
                    XUẤT KHO ĐỦ ĐƠN HÀNG
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL XÁC NHẬN XUẤT KHO (Thay thế confirm cũ) -->
<div class="modal fade" id="modalXacNhanXuat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">XÁC NHẬN XUẤT KHO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-5">
                <h5 class="mt-4">Bạn có chắc chắn muốn xuất kho <span id="soMucChon">0</span> mục đã chọn?</h5>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" id="btnXacNhanXuatThuc" class="btn btn-danger btn-lg px-5">Xác nhận xuất
                    kho</button>
                <button type="button" class="btn btn-secondary px-5" data-bs-dismiss="modal">Hủy bỏ</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PHIẾU XUẤT KHO -->
<div class="modal fade" id="modalPhieuXuat" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title fw-bold">PHIẾU XUẤT KHO</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="noiDungPhieu"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary btn-lg" onclick="window.print()">In Phiếu Xuất Kho</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.donhang-row').forEach(function(row) {
    row.onclick = function(e) {
        if (e.target.tagName === 'INPUT') return;
        var cb = row.querySelector('.donhang-checkbox');
        cb.checked = !cb.checked;
        row.classList.toggle('table-active', cb.checked);
        capNhatSoLuongChon();
    };
});

function capNhatSoLuongChon() {
    var count = document.querySelectorAll('.donhang-checkbox:checked').length;
    document.getElementById('soMucChon').textContent = count;
}

// Cập nhật số lượng khi mở modal xác nhận
var modalXacNhan = document.getElementById('modalXacNhanXuat');
modalXacNhan.addEventListener('show.bs.modal', function() {
    capNhatSoLuongChon();
});

// Nút xác nhận thực sự xuất kho
document.getElementById('btnXacNhanXuatThuc').addEventListener('click', function() {
    var items = [];
    document.querySelectorAll('.donhang-checkbox:checked').forEach(function(cb) {
        var v = cb.value.split('_');
        items.push({
            maDH: v[0],
            maSP: v[1],
            soLuong: v[2],
            donGia: v[3]
        });
    });

    if (items.length === 0) {
        alert('Vui lòng chọn ít nhất 1 mục!');
        return;
    }

    // Đóng modal xác nhận
    var modalInstance = bootstrap.Modal.getInstance(modalXacNhan);
    modalInstance.hide();

    fetch('xuly_xuatkho.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                items: items
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('noiDungPhieu').innerHTML = data.html;
                var modalPhieu = new bootstrap.Modal(document.getElementById('modalPhieuXuat'));
                modalPhieu.show();

                document.getElementById('modalPhieuXuat').addEventListener('hidden.bs.modal', function() {
                    location.reload();
                });
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(() => alert('Lỗi kết nối đến server!'));
});
</script>

<?php include_once('../../layout/footer.php'); ?>