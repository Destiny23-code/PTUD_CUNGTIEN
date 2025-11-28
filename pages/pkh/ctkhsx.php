<?php
include_once("../../layout/giaodien/pkh.php");
include_once("../../class/clskehoachsx.php");

// 1. KHỞI TẠO BIẾN
$donhang_chitiet = null;
$sanpham_chitiet = array();
$nguyenlieu_chitiet = array();
$maKHSX_xem = '';
if (isset($_GET['xemchitiet']) && !empty($_GET['xemchitiet'])) {
    $maKHSX_xem = $_GET['xemchitiet'];
}

// 2. LẤY DỮ LIỆU
if ($maKHSX_xem != '') {
    $kehoachModel = new KeHoachModel();
    $chitiet = $kehoachModel->getChiTietKeHoach($maKHSX_xem);

    $donhang_chitiet = !empty($chitiet['thongtin']) ? $chitiet['thongtin'] : null;
    $sanpham_chitiet = !empty($chitiet['sanpham']) ? $chitiet['sanpham'] : array();
    $nguyenlieu_chitiet = !empty($chitiet['nguyenlieu']) ? $chitiet['nguyenlieu'] : array();
}

// 3. HÀM HIỂN THỊ BADGE TRẠNG THÁI
function getBadgeClass($trangThai) {
    switch (mb_strtolower(trim($trangThai),'UTF-8')) {
        case 'hoàn thành': return 'bg-success';
        case 'đã duyệt': return 'bg-success';
        case 'đang thực hiện': return 'bg-warning text-dark';
        case 'trễ hạn': return 'bg-danger';
        case 'từ chối': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// Nếu không có kế hoạch, hiển thị thông báo
if (!$donhang_chitiet) {
    echo "<div class='alert alert-warning'>Không tìm thấy kế hoạch.</div>";
    exit;
}
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-primary"><i class="bi bi-clipboard-data me-2"></i>Chi tiết Kế hoạch Sản Xuất</h5>
        <a href="dskh.php" class="btn btn-back"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <!-- Thông tin chung -->
    <div class="card mb-4" style =" border-radius: 12px;border: none;box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);">
        <div class="card-body">
            <div class="text-primary fw-semibold mb-2"><i class="bi bi-info-circle me-2"></i>Thông tin chung</div>
            <div class="row">
                <div class="col-md-3 mb-2"><strong>Mã kế hoạch:</strong> <?php echo htmlspecialchars($donhang_chitiet['maKHSX']); ?></div>
                <div class="col-md-3 mb-2"><strong>Ngày lập:</strong> <?php echo htmlspecialchars($donhang_chitiet['ngayLap']); ?></div>
                <div class="col-md-3 mb-2"><strong>Người lập:</strong> <?php echo htmlspecialchars($donhang_chitiet['nguoiLap']); ?></div>
                <div class="col-md-3 mb-2">
                    <strong>Trạng thái:</strong>
                    <span class="badge <?php echo getBadgeClass($donhang_chitiet['trangThai']); ?>">
                        <?php echo htmlspecialchars($donhang_chitiet['trangThai']); ?>
                    </span>
                </div>
                <div class="col-md-12 mt-2"><strong>Ghi chú:</strong> <?php echo htmlspecialchars($donhang_chitiet['ghiChu']); ?></div>
            </div>
        </div>
    </div>

    <!-- Thông tin sản phẩm -->
    <div class="card mb-4" style =" border-radius: 12px;border: none;box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);">
        <div class="card-body">
            <div class="text-primary fw-semibold mb-2"><i class="bi bi-gear me-2"></i>Thông tin sản xuất</div>
            <?php if (!empty($sanpham_chitiet)) { ?>
                <div class="row">
                    <?php foreach($sanpham_chitiet as $sp) { ?>
                        <div class="col-md-3 mb-2"><strong>Sản phẩm:</strong> <?php echo htmlspecialchars($sp['tenSP']); ?></div>
                        <div class="col-md-3 mb-2"><strong>Số lượng:</strong> <?php echo htmlspecialchars($sp['soLuong']); ?></div>   
                    <?php } ?>
                </div>
            <?php } 
            else { ?>
                <p class="text-muted">Chưa có sản phẩm cho kế hoạch này.</p>
            <?php } ?>
        </div>
    </div>

    <!-- Nguyên liệu -->
    <div class="card" style =" border-radius: 12px;border: none;box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);">
        <div class="card-body">
            <div class="text-primary fw-semibold mb-2"><i class="bi bi-box-seam me-2"></i>Nguyên liệu sử dụng</div>
            <table class="table table-bordered table-hover align-middle mb-0 text-center">
                <thead class="table-primary text-center">
                    <tr>
                        <th>#</th>
                        <th style="width:8%">Mã NL</th>
                        <th>Tên nguyên liệu</th>
                        <th>Đơn vị</th>
                        <th>Số lượng/1 SP</th>
                        <th>Tổng SL cần</th>
                        <th>Tồn kho</th>
                        <th>Thiếu hụt</th>
                        <th>Phương án xử lý</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($nguyenlieu_chitiet)) {
                        $i=1;
                        foreach($nguyenlieu_chitiet as $nl) { ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($nl['maNL']); ?></td>
                                <td><?php echo htmlspecialchars($nl['tenNL']); ?></td>
                                <td><?php echo htmlspecialchars($nl['donViTinh']); ?></td>
                                <td><?php echo htmlspecialchars($nl['soLuong1SP']); ?></td>
                                <td class="bg-warning-subtle"><?php echo htmlspecialchars($nl['tongSLCan']); ?></td>
                                <td><?php echo htmlspecialchars($nl['slTonTaiKho']); ?></td>
                                <td class="<?php echo ($nl['slThieuHut'] > 0 ? 'text-danger fw-bold' : ''); ?>">
                                    <?php echo htmlspecialchars($nl['slThieuHut']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($nl['phuongAnXuLy']); ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr><td colspan="9" class="text-muted">Chưa có nguyên liệu cho kế hoạch này.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once("../../layout/footer.php"); ?>
