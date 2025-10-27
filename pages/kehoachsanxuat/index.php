<?php
// Giả định các file layout và class ketnoi đã được include đúng đường dẫn
include_once('../../layout/giaodien/pkh.php'); 
include_once('../../class/clsconnect.php'); 

$ketnoiObj = new ketnoi(); 
$conn = $ketnoiObj->connect();

// 1. LẤY DANH SÁCH ĐƠN HÀNG Ở TRẠNG THÁI 'Mới tạo'
$sql_dh = "SELECT 
    d.maDH,
    c.maSP,
    s.tenSP,
    s.loaiSP,
    s.donViTinh,
    s.moTa,
    d.soLuong,
    d.ngayGiaoDuKien,
    d.trangThai
FROM DONHANG d
JOIN CHITIET_DONHANG c ON d.maDH = c.maDH
JOIN SANPHAM s ON c.maSP = s.maSP
WHERE d.trangThai = 'Mới tạo'
ORDER BY d.ngayDat DESC";

$danhsach_dh = $ketnoiObj->laydulieu($conn, $sql_dh);
$conn->close();
?>

<?php
$maSP = isset($_GET['maSP']) ? $_GET['maSP'] : '';


if ($maSP != '') {
    // ✅ Lấy danh sách nguyên liệu của sản phẩm
    $sqlNL = "
        SELECT n.maNL, n.tenNL, n.donViTinh, n.soLuongTon, ns.soLuongCan
        FROM NGUYENLIEU_SP ns
        JOIN NGUYENLIEU n ON ns.maNL = n.maNL
        WHERE ns.maSP = '$maSP'
    ";
    $nguyenlieu = $ketnoiObj->laydulieu($conn, $sqlNL);

    // ✅ Lấy danh sách xưởng phụ trách
    $sqlXuong = "
        SELECT x.tenXuong
        FROM SANPHAM_XUONG sx
        JOIN XUONG x ON sx.maXuong = x.maXuong
        WHERE sx.maSP = '$maSP'
    ";
    $xuong = $ketnoiObj->laydulieu($conn, $sqlXuong);

echo json_encode(array('nguyenlieu' => $nguyenlieu, 'xuong' => $xuong));

}
$conn->close();
?>


<div class="content">
    <h5 class="fw-bold text-primary">
        <i class="bi bi-calendar-check-fill me-2"></i>Lập & Điều chỉnh Kế hoạch Sản xuất
    </h5>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white fw-bold">
            Danh sách đơn hàng chờ lập kế hoạch
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover align-middle m-0 text-center">
                <thead class="thead-blue">
                    <tr>
                        <th style="width:5%">Chọn</th>
                        <th style="width:15%">Mã đơn hàng</th>
                        <th style="width:15%">Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th style="width:10%">ĐVT</th>
                        <th style="width:10%">Số lượng</th>
                        <th style="width:15%">Ngày giao dự kiến</th>
                        <th style="width:10%">Trạng thái</th>
                        <th>Cảnh báo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // ⭐️ BƯỚC 1: LỌC VÀ HIỂN THỊ CÁC ĐƠN HÀNG 'MỚI TẠO'
                    if (is_array($danhsach_dh) && count($danhsach_dh) > 0) {
                        foreach ($danhsach_dh as $row) {
                            $maDH_SP = $row['maDH'] . '_' . $row['maSP']; // ID duy nhất cho mỗi dòng
                            
                            echo "<tr>";
                            // Input checkbox được dùng để chọn dòng và chứa dữ liệu quan trọng
                            echo "<td><input type='checkbox' name='selected_items[]' value='{$maDH_SP}' class='order-checkbox'></td>"; 
                            echo "<td>" . htmlspecialchars($row['maDH']) . "</td>";
                            echo "<td class='maSP'>" . htmlspecialchars($row['maSP']) . "</td>";
                            echo "<td class='tenSP'>" . htmlspecialchars($row['tenSP']) . "</td>";
                            echo "<td class='dvt'>" . htmlspecialchars($row['donViTinh']) . "</td>";
                            echo "<td class='soLuong'>" . htmlspecialchars($row['soLuong']) . "</td>";
                            echo "<td class='ngayGiao'>" . htmlspecialchars($row['ngayGiaoDuKien']) . "</td>";
                            echo "<td><span class='badge bg-info text-dark'>" . htmlspecialchars($row['trangThai']) . "</span></td>";
                            echo "<td><span class='badge bg-warning text-dark'>" . htmlspecialchars($row['canhBao']) . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-muted'>Không có đơn hàng nào ở trạng thái 'Mới tạo'.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="plan-form-container" class="card shadow-sm" style="display:none;">
        <div class="card-header bg-primary text-white fw-bold">
            Thiết lập kế hoạch sản xuất
        </div>
        <div class="card-body">
            <form>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Đơn hàng/Sản phẩm</label>
                        <input type="text" class="form-control" id="form_maDH_SP" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Người lập</label>
                        <input type="text" class="form-control" value="<?php session_start(); echo htmlspecialchars($_SESSION['hoTen']); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ngày lập</label>
                        <input type="text" class="form-control" value="<?php echo date('d/m/Y'); ?>" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hình thức sản xuất</label>
                        <select class="form-select">
                            <option>Theo lô</option>
                            <option>Theo đơn hàng</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ngày bắt đầu</label>
                        <input type="date" class="form-control" id="ngayBatDau">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ngày kết thúc</label>
                        <input type="date" class="form-control" id="ngayKetThuc">
                    </div>
                </div>
                <hr>
                <h6 class="fw-bold text-primary">Danh sách sản phẩm</h6>
                <table class="table table-bordered table-sm mt-2">
                    <thead>
                        <tr class="table-primary text-center">
                            <th>Chọn</th>
                            <th>Mã SP</th>
                            <th>Tên sản phẩm</th>
                            <th>Loại</th>
                            <th>DVT</th>
                            <th>Mô tả</th>
                        </tr>
                    </thead>
                    <tbody id="" class="text-center">
                        <?php 
                    if (is_array($danhsach_dh) && count($danhsach_dh) > 0) {
                        foreach ($danhsach_dh as $row) {
                            echo "<tr>";
                            echo "<td><input type='checkbox' name='chonSP' class='chonSP'></td>";
                            echo "<td class='maSP'>" . htmlspecialchars($row['maSP']) . "</td>";
                            echo "<td class='tenSP'>" . htmlspecialchars($row['tenSP']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['loaiSP']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['donViTinh']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['moTa']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-muted text-center'>Không có sản phẩm nào trong cơ sở dữ liệu.</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <hr>
                
                <h6 class="fw-bold text-primary">Nguyên liệu cần cho sản xuất</h6>
                <table class="table table-bordered table-sm mt-2">
                    <thead>
                        <tr class="table-primary">
                            <th>Mã NL</th>
                            <th>Tên NL</th>
                            <th>ĐVT</th>
                            <th>Số lượng cần</th>
                            <th>Số lượng tồn</th>
                            <th>Thiếu hụt</th>
                            <th>Phương án xử lý</th>
                        </tr>
                    </thead>
                    <tbody id="nguyen-lieu-body">
                        <tr>
                            <td>NL001</td>
                            <td>Nguyên liệu A</td>
                            <td>Kg</td>
                            <td id="nl_sl_can">50</td>
                            <td>30</td>
                            <td class="text-danger">20</td>
                            <td><input type="text" class="form-control form-control-sm" value="Mua bổ sung"></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="mt-3">
                    <label class="form-label fw-semibold">Ghi chú điều chỉnh</label>
                    <textarea class="form-control" rows="3" placeholder="Nhập ghi chú nếu cần..."></textarea>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-secondary me-2">Làm mới</button>
                    <button type="submit" class="btn btn-success">Lưu Kế hoạch</button>
                </div>
            </form>
        </div>
    </div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    const formContainer = document.getElementById('plan-form-container');
    const formMaDHSP = document.getElementById('form_maDH_SP');
    
    // Đính kèm sự kiện cho tất cả các checkbox
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Khi có bất kỳ checkbox nào được check/bỏ check
            const checkedBoxes = Array.from(checkboxes).filter(cb => cb.checked);

            if (checkedBoxes.length > 0) {
                // Chỉ xử lý checkbox được check cuối cùng
                const checked = checkedBoxes[checkedBoxes.length - 1]; 
                
                // 1. Lấy dữ liệu từ dòng đã chọn
                const row = checked.closest('tr');
                const maDH = row.querySelector('td:nth-child(2)').textContent;
                const tenSP = row.querySelector('.tenSP').textContent;
                
                // Cập nhật thông tin đơn hàng trên form
                formMaDHSP.value = maDH + ' - ' + tenSP; 
                
                // Hiển thị form
                formContainer.style.display = 'block';
                
                // 2. Tắt các checkbox khác (chỉ cho phép chọn 1 đơn hàng để lập kế hoạch)
                checkboxes.forEach(cb => {
                    if (cb !== checked) {
                        cb.checked = false; 
                    }
                });

                // 3. (Cần AJAX tại đây):
                // Gửi request AJAX với maDH và maSP đến một file PHP khác (ví dụ: fetch_materials.php)
                // File PHP đó sẽ JOIN CT_DONHANG và NGUYENLIEU_SP để lấy danh sách nguyên liệu
                // và trả về HTML hoặc JSON để cập nhật #nguyen-lieu-body.

            } else {
                // Ẩn form nếu không có checkbox nào được chọn
                formContainer.style.display = 'none';
            }
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    const formContainer = document.getElementById('plan-form-container');
    const formMaDHSP = document.getElementById('form_maDH_SP');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checked = this.checked;

            // Chỉ chọn 1 sản phẩm tại 1 thời điểm
            checkboxes.forEach(cb => { if (cb !== this) cb.checked = false; });

            if (checked) {
                const row = this.closest('tr');
                const maSP = row.querySelector('.maSP').textContent.trim();
                const tenSP = row.querySelector('.tenSP').textContent.trim();
                const maDH = row.querySelector('td:nth-child(2)').textContent.trim();

                // Cập nhật form tiêu đề
                formContainer.style.display = 'block';
                formMaDHSP.value = maDH + ' - ' + tenSP;

                // --- GỌI AJAX ---
                fetch('fetch_info.php?maSP=' + maSP)
                    .then(res => res.json())
                    .then(data => {
                        // 🔹 Xưởng phụ trách
                        const xuongContainer = document.querySelector('.col-md-9');
                        xuongContainer.innerHTML = '<label class="form-label">Xưởng phụ trách</label><br>';
                        if (data.xuong && data.xuong.length > 0) {
                            data.xuong.forEach(x => {
                                xuongContainer.innerHTML += `
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" checked name="xuong[]" value="${x.tenXuong}">
                                        <label class="form-check-label">${x.tenXuong}</label>
                                    </div>`;
                            });
                        } else {
                            xuongContainer.innerHTML += '<span class="text-muted">Không có xưởng phụ trách</span>';
                        }

                        // 🔹 Nguyên liệu
                        const nlBody = document.querySelector('#nguyen-lieu-body');
                        nlBody.innerHTML = '';
                        if (data.nguyenlieu && data.nguyenlieu.length > 0) {
                            data.nguyenlieu.forEach((nl, index) => {
                                const thieu = nl.soLuongCan - nl.soLuongTon;
                                nlBody.innerHTML += `
                                    <tr>
                                        <td>${nl.maNL}</td>
                                        <td>${nl.tenNL}</td>
                                        <td>${nl.donViTinh}</td>
                                        <td>${nl.soLuongCan}</td>
                                        <td>${nl.soLuongTon}</td>
                                        <td class="${thieu > 0 ? 'text-danger' : 'text-success'}">${thieu > 0 ? thieu : 0}</td>
                                        <td><input type="text" class="form-control form-control-sm" value="${thieu > 0 ? 'Mua thêm' : 'Đủ'}"></td>
                                    </tr>`;
                            });
                        } else {
                            nlBody.innerHTML = '<tr><td colspan="7" class="text-muted">Không có nguyên liệu cho sản phẩm này</td></tr>';
                        }
                    });
            } else {
                formContainer.style.display = 'none';
            }
        });
    });
});
</script>
