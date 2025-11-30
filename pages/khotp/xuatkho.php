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
                <button type="button" id="btnXuat" class="btn btn-success shadow-lg">
                    XUẤT KHO ĐỦ ĐƠN HÀNG
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL PHIẾU XUẤT KHO - CÓ NÚT X ĐÓNG -->
<div class="modal fade" id="modalPhieuXuat" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title fw-bold">PHIẾU XUẤT KHO</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="noiDungPhieu">
                <!-- Nội dung phiếu sẽ được chèn bằng JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    In Phiếu Xuất Kho
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Click dòng chọn checkbox
document.querySelectorAll('.donhang-row').forEach(function(row) {
    row.onclick = function(e) {
        if (e.target.tagName === 'INPUT') return;
        var cb = row.querySelector('.donhang-checkbox');
        cb.checked = !cb.checked;
        row.classList.toggle('table-active', cb.checked);
    };
});

// Nút xuất kho
document.getElementById('btnXuat').onclick = function() {
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
        alert('Vui lòng chọn ít nhất 1 mục để xuất kho!');
        return;
    }

    if (!confirm('Xác nhận xuất kho ' + items.length + ' mục đã chọn?')) return;

    fetch('xuly_xuatkho.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                items: items
            })
        })
        .then(function(r) {
            return r.json();
        })
        .then(function(data) {
            if (data.status === 'error') {
                alert('Lỗi: ' + data.message);
            } else {
                // HIỆN PHIẾU TRONG MODAL CÓ NÚT X
                document.getElementById('noiDungPhieu').innerHTML = data.html;
                var modal = new bootstrap.Modal(document.getElementById('modalPhieuXuat'));
                modal.show();

                // Khi đóng modal → reload trang để cập nhật danh sách
                document.getElementById('modalPhieuXuat').addEventListener('hidden.bs.modal', function() {
                    location.reload();
                });
            }
        })
        .catch(function(err) {
            alert('Lỗi kết nối: ' + err);
        });
};
</script>

<?php include_once('../../layout/footer.php'); ?>