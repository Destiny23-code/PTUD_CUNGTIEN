<?php
// 1. INCLUDE CÁC FILE CẦN THIẾT
include_once("../../layout/giaodien/pkh.php"); 
include_once("../../class/clskehoachsx.php"); 
include_once("../../class/clsconnect.php");
include_once("../../class/clsDonHang.php"); 

// 2. KHỞI TẠO BIẾN
$donhang_chitiet = null;
$sanpham_chitiet = array();
$maDH_xem = '';
if (isset($_GET['xemchitiet']) && !empty($_GET['xemchitiet'])) {
    $maDH_xem = $_GET['xemchitiet'];
}

// 3. LẤY DỮ LIỆU
if ($maDH_xem != '') {
    $kehoachModel = new KeHoachModel();
    $chitiet = $kehoachModel->getChiTietDonHang($maDH_xem);

    $donhang_chitiet = !empty($chitiet['thongtin']) ? $chitiet['thongtin'] : null;
    $sanpham_chitiet = !empty($chitiet['sanpham']) ? $chitiet['sanpham'] : array();
}

// 4. HÀM HIỂN THỊ BADGE TRẠNG THÁI
function getBadgeClass($trangThai) {
    switch ($trangThai) {
        case 'Hoàn thành': return 'bg-success';
        case 'Đang sản xuất': return 'bg-warning text-dark';
        case 'Mới tạo': return 'bg-info text-dark';
        case 'Đã hủy': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// 5. XỬ LÝ HIỂN THỊ MODAL (ĐÃ KHÔI PHỤC)
$showDeleteModal = false;
$showUpdateModal = false;
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete') $showDeleteModal = true;
    if ($_GET['action'] === 'update') $showUpdateModal = true;
}

// ===============================================
// XỬ LÝ CẬP NHẬT ĐƠN HÀNG (POST)
// ===============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['maDH'], $_POST['maSP'], $_POST['soLuong'], $_POST['ngayGiaoDuKien'])) {
        $maDH = $_POST['maDH'];
        $maSP = $_POST['maSP'];
        $soLuong = intval($_POST['soLuong']);
        $ngayGiaoDuKien = $_POST['ngayGiaoDuKien'];

        $dh = new DonHang();
         // 1. Cập nhật số lượng cho sản phẩm cụ thể trong chitiet_donhang
        $updateSP = $dh->thucthisql(
            "UPDATE chitiet_donhang 
             SET soLuong = '$soLuong' 
             WHERE maDH = '$maDH' AND maSP = '$maSP'"
        );

        // 2. Cập nhật ngày giao dự kiến trong bảng donhang
        $updateDH = $dh->thucthisql(
            "UPDATE donhang 
             SET ngayGiaoDuKien = '$ngayGiaoDuKien' 
             WHERE maDH = '$maDH'"
        );

        // Nếu cả 2 đều thành công, redirect success
        $ketqua = $updateSP && $updateDH;

        header("Location: ctdh.php?xemchitiet=$maDH&update=" . ($ketqua ? "success" : "fail"));
        exit;
    }
}

// ===============================================
// XỬ LÝ XÓA ĐƠN HÀNG (GET)
// ===============================================
if (isset($_GET['deleteDH'], $_GET['maDH'], $_GET['lyDo'])) {
    $maDH = trim($_GET['maDH']);
    $lyDo = trim($_GET['lyDo']);

    $dh = new DonHang();

    // 1. Cập nhật trạng thái đơn hàng thành 'Đã hủy' và thêm lý do vào ghi chú
    // Nếu ghiChu cũ đã có, sẽ nối thêm lý do
    $sql = "UPDATE donhang 
            SET trangThai = 'Đã hủy', 
                ghiChu = CONCAT(IFNULL(ghiChu, ''), '$lyDo') 
            WHERE maDH = '$maDH'";

    $ketqua = $dh->thucthisql($sql);

    // Chuyển hướng về danh sách
    header("Location: dsdh.php?delete=" . ($ketqua ? "success" : "fail"));
    exit;
}
?>

<div class="content">

<?php if ($donhang_chitiet): ?>
    <?php $badgeClass = getBadgeClass($donhang_chitiet['trangThai']); ?>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-primary">
            <i class="bi bi-file-earmark-text me-2"></i>Chi tiết Đơn hàng: 
            <strong><?php echo htmlspecialchars($donhang_chitiet['maDH']); ?></strong>
        </h5>
        <a href="dsdh.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="card mb-3 shadow-sm">
        <div class="card-header bg-secondary text-white fw-semibold">
            <i class="bi bi-info-circle me-2"></i>Thông tin chung
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3"><strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($donhang_chitiet['maDH']); ?></div>
                <div class="col-md-3"><strong>Ngày đặt:</strong> <?php echo htmlspecialchars($donhang_chitiet['ngayDat']); ?></div>
                <div class="col-md-3"><strong>Ngày giao dự kiến:</strong> <?php echo htmlspecialchars($donhang_chitiet['ngayGiaoDuKien']); ?></div>
                <div class="col-md-3"><strong>Trạng thái:</strong> 
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($donhang_chitiet['trangThai']); ?></span>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-8"><strong>Ghi chú:</strong> <?php echo htmlspecialchars($donhang_chitiet['ghiChu']); ?></div>
            </div>
        </div>
    </div>

    <div class="card mb-3 shadow-sm">
        <div class="card-header bg-secondary text-white fw-semibold">
            <i class="bi bi-person-vcard me-2"></i>Thông tin khách hàng
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-4"><strong>Tên khách hàng:</strong> <?php echo htmlspecialchars($donhang_chitiet['tenKH']); ?></div>
                <div class="col-md-4"><strong>Email:</strong> <?php echo htmlspecialchars($donhang_chitiet['email']); ?></div>
                <div class="col-md-4"><strong>Điện thoại:</strong> <?php echo htmlspecialchars($donhang_chitiet['sDT']); ?></div>
            </div>
            <div class="row">
                <div class="col-12"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($donhang_chitiet['diaChi']); ?></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-info fw-semibold text-dark">
            <i class="bi bi-box-seam me-2"></i>Danh sách sản phẩm
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0 text-center">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>#</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Đơn vị</th>
                        <th>Số lượng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sanpham_chitiet)): $stt=1; ?>
                        <?php foreach ($sanpham_chitiet as $sp): ?>
                        <tr>
                            <td><?php echo $stt++; ?></td>
                            <td><?php echo htmlspecialchars($sp['maSP']); ?></td>
                            <td><?php echo htmlspecialchars($sp['tenSP']); ?></td>
                            <td><?php echo htmlspecialchars($sp['donViTinh']); ?></td>
                            <td><?php echo number_format($sp['soLuong']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-muted">Không có sản phẩm</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 p-3 bg-light rounded d-flex justify-content-end gap-2">
        <?php if ($donhang_chitiet['trangThai'] === 'Mới tạo'): ?>
            <form method="GET" action="ctdh.php" style="display:inline;">
                <input type="hidden" name="xemchitiet" value="<?php echo $maDH_xem; ?>">
                <input type="hidden" name="action" value="update">
                <button type="submit" class="btn btn-warning"><i class="bi bi-pencil me-1"></i> </button>
            </form>

            <form method="GET" action="ctdh.php" style="display:inline;">
                <input type="hidden" name="xemchitiet" value="<?php echo $maDH_xem; ?>">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i> </button>
            </form>
        <?php else: ?>
            <p class="text-muted mb-0">Chỉ có thể Sửa hoặc Xóa đơn hàng ở trạng thái <strong>"Mới tạo"</strong>.</p>
        <?php endif; ?>
    </div>

<?php else: ?>
    <div class="alert alert-danger shadow-sm">
        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Lỗi</h5>
        <p>Không tìm thấy đơn hàng với mã <strong><?php echo htmlspecialchars($maDH_xem); ?></strong> hoặc đơn hàng không tồn tại.</p>
        <a href="dsdh.php" class="btn btn-danger">Quay lại danh sách</a>
    </div>
<?php endif; ?>

</div>

<div class="modal fade <?php echo $showDeleteModal ? 'show d-block' : ''; ?>" id="modalDeleteOrder" tabindex="-1" <?php echo $showDeleteModal ? 'style="background: rgba(0,0,0,.5);"' : ''; ?> aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-trash3-fill me-2"></i>Hủy đơn hàng</h6>
                <a href="ctdh.php?xemchitiet=<?php echo $maDH_xem; ?>" class="btn-close btn-close-white"></a>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5 me-2"></i>
                    <div><strong>Cảnh báo:</strong> Hành động này sẽ xóa vĩnh viễn đơn hàng khỏi hệ thống. Vui lòng kiểm tra kỹ.</div>
                </div>
                <div class="border rounded p-3 bg-light">
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold">Mã đơn hàng:</div>
                        <div class="col-7 text-dark"><?php echo htmlspecialchars($donhang_chitiet['maDH']); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 fw-semibold">Ngày đặt:</div>
                        <div class="col-7 text-dark"><?php echo htmlspecialchars($donhang_chitiet['ngayDat']); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-5 fw-semibold">Trạng thái:</div>
                        <div class="col-7"><span><?php echo htmlspecialchars($donhang_chitiet['trangThai']); ?></span></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="deleteReason" class="form-label fw-semibold mt-1">Lý do hủy <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="lyDo" id="deleteReason" rows="2" required></textarea>
                    <div id="deleteReasonError" class="text-danger small mt-1 d-none"></div>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="confirmDeleteCheckbox">
                    <label class="form-check-label text-danger small" for="confirmDeleteCheckbox">
                        Tôi xác nhận muốn hủy đơn hàng này.
                    </label>
                </div>
                <div class="mt-3 text-danger small">⚠️ Chỉ có thể xóa đơn hàng có <strong>trạng thái: “Mới tạo”</strong>.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.location='ctdh.php?xemchitiet=<?php echo $maDH_xem; ?>'">Đóng</button>
                <button type="button" class="btn btn-danger"
                    onclick="
                        // SỬ DỤNG JAVASCRIPT NỘI TUYẾN ĐƠN GIẢN ĐỂ KIỂM TRA ĐIỀU KIỆN
                        let lydo = document.getElementById('deleteReason').value;
                        let confirmChecked = document.getElementById('confirmDeleteCheckbox').checked;
                        let errorDiv = document.getElementById('deleteReasonError');
                        errorDiv.classList.add('d-none');

                        if (lydo.trim() === '') {
                            errorDiv.textContent = '⚠️ Vui lòng nhập lý do trước khi xác nhận hủy.';
                            errorDiv.classList.remove('d-none');
                            return;
                        }

                        if (!confirmChecked) {
                            errorDiv.textContent = '⚠️ Bạn phải xác nhận hủy đơn hàng.';
                            errorDiv.classList.remove('d-none');
                            return;
                        }

                        // Nếu hợp lệ, chuyển hướng để gửi yêu cầu xóa qua GET
                        window.location = 'ctdh.php?deleteDH=1&maDH=<?php echo $maDH_xem; ?>&lyDo=' + encodeURIComponent(lydo);
                    ">
                    <i class="bi bi-trash-fill me-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade <?php echo $showUpdateModal ? 'show d-block' : ''; ?>" id="modalUpdateOrder" tabindex="-1" <?php echo $showUpdateModal ? 'style="background: rgba(0,0,0,.5);"' : ''; ?> aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Cập nhật đơn hàng</h6>
                <a href="ctdh.php?xemchitiet=<?php echo $maDH_xem; ?>" class="btn-close"></a>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="maDH" value="<?php echo $maDH_xem; ?>">
                    <div class="mb-3 bg-light border rounded p-3">
                        <div class="small">⚠️ Chỉ có thể thay đổi số lượng của sản phẩm đã chọn và ngày giao dự kiến của đơn hàng.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Chọn sản phẩm</label>
                        <select name="maSP" class="form-select form-select-sm" required>
                            <?php 
                            // Nếu không dùng JS để tự điền, ta mặc định chọn sản phẩm đầu tiên
                            $initial_soLuong = 0; 
                            foreach($sanpham_chitiet as $index => $sp): 
                                if ($index === 0) { $initial_soLuong = intval($sp['soLuong']); }
                            ?>
                                <option value="<?php echo htmlspecialchars($sp['maSP']); ?>">
                                    <?php echo htmlspecialchars($sp['tenSP']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Số lượng</label>
                        <input type="number" name="soLuong" class="form-control form-control-sm" min="1" 
                            value="<?php echo $initial_soLuong; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ngày giao dự kiến</label>
                        <input type="date" name="ngayGiaoDuKien" class="form-control form-control-sm" 
                            value="<?php echo htmlspecialchars($donhang_chitiet['ngayGiaoDuKien']); ?>" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning btn-sm text-dark"><i class="bi bi-save me-1"></i> Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>