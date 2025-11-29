<?php
include_once('../../layout/giaodien/khotp.php');
require_once('../../class/clsconnect.php');

if (!isset($_SESSION['hoTen'])) {
    header('Location: ../../pages/dangnhap/dangnhap.php');
    exit;
}

// SỬA: Dùng cách gọi ổn định, không cần sửa clsconnect.php
$ketnoi_instance = new ketnoi();
$conn = $ketnoi_instance->connect();

// Chỉ hiện đơn khi đủ 100% tồn kho để giao 1 lần
$sql = "
SELECT 
    dh.maDH, dh.ngayGiaoDuKien, kh.tenKH, kh.diaChi, kh.sDT,
    ct.maSP, sp.tenSP, ct.soLuong AS slCanXuat, ct.donGia,
    sp.soLuongTon
FROM donhang dh
JOIN chitiet_donhang ct ON dh.maDH = ct.maDH
JOIN khachhang kh ON dh.maKH = kh.maKH
JOIN sanpham sp ON ct.maSP = sp.maSP
WHERE dh.trangThai NOT IN ('Hoàn thành','Đã hủy')
  AND ct.soLuong <= sp.soLuongTon
GROUP BY dh.maDH, ct.maSP
ORDER BY dh.ngayGiaoDuKien ASC
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
            Xuất kho - Chỉ xuất khi đủ 100% đơn hàng
        </h5>

        <?php if ($rs->num_rows == 0): ?>
        <div class="alert alert-warning text-center py-5">
            Chưa có đơn hàng nào đủ hàng để xuất kho.
        </div>
        <?php endif; ?>

        <form id="frmXuatKho">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Chọn</th>
                            <th>Mã ĐH</th>
                            <th>Khách hàng</th>
                            <th>Sản phẩm</th>
                            <th class="text-center">SL xuất</th>
                            <th class="text-center">Tồn kho</th>
                            <th class="text-center">Ngày giao</th>
                        </tr>
                    </thead>
                    <tbody id="dsDonHang">
                        <?php while ($row = $rs->fetch_assoc()): ?>
                        <tr class="donhang-row">
                            <td class="text-center">
                                <input type="checkbox" class="donhang-checkbox"
                                    value="<?= $row['maDH'] ?>_<?= $row['maSP'] ?>_<?= $row['slCanXuat'] ?>_<?= $row['donGia'] ?>">
                            </td>
                            <td><strong>#<?= str_pad($row['maDH'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= htmlspecialchars($row['tenKH']) ?></td>
                            <td><?= htmlspecialchars($row['tenSP']) ?></td>
                            <td class="text-end fw-bold"><?= number_format($row['slCanXuat']) ?></td>
                            <td class="text-end text-success fw-bold"><?= number_format($row['soLuongTon']) ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($row['ngayGiaoDuKien'])) ?></td>
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

        <!-- Phiếu xuất kho -->
        <div id="phieuXuatKho" class="card shadow mt-5 d-none">
            <div class="card-body p-5" id="thongTinPhieu"></div>
        </div>
    </div>
</div>

<script>
document.getElementById("btnXuat").addEventListener("click", function() {
    const items = [];
    document.querySelectorAll(".donhang-checkbox:checked").forEach(cb => {
        const [dh, sp, sl, dg] = cb.value.split('_');
        items.push({
            maDH: dh,
            maSP: sp,
            soLuong: sl,
            donGia: dg
        });
    });

    if (items.length === 0) return alert("Vui lòng chọn ít nhất 1 đơn hàng!");

    if (!confirm(`Xuất kho ${items.length} đơn hàng?`)) return;

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
            if (data.status === 'error') return alert(data.message);
            document.getElementById('thongTinPhieu').innerHTML = data.html;
            document.getElementById('phieuXuatKho').classList.remove('d-none');
            alert('Xuất kho thành công!');
        })
        .catch(err => alert('Lỗi kết nối: ' + err));
});

// Click dòng để chọn
document.querySelectorAll('.donhang-row').forEach(row => {
    row.onclick = function(e) {
        if (e.target.tagName === 'INPUT') return;
        const cb = row.querySelector('.donhang-checkbox');
        cb.checked = !cb.checked;
        row.classList.toggle('table-active', cb.checked);
    };
});
</script>

<?php include_once('../../layout/footer.php'); ?>