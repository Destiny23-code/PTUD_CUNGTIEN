<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();

require_once("../../class/clslogin.php");
require_once("../../class/clsPBDC.php");

$p = new login();
$session_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
$session_user = isset($_SESSION['user']) ? $_SESSION['user'] : '';
$session_pass = isset($_SESSION['pass']) ? $_SESSION['pass'] : '';
$session_phanquyen = isset($_SESSION['phanquyen']) ? $_SESSION['phanquyen'] : 0;

if (!$p->confirmlogin($session_id, $session_user, $session_pass, $session_phanquyen) || $session_phanquyen != 2) {
    header("Location: ../dangnhap.php");
    exit();
}

include_once('../../layout/giaodien/qdx.php');

$phanBo = new PhanBoDayChuyen();

// Xử lý cập nhật trạng thái
if (isset($_GET['action']) && $_GET['action'] == 'update_status') {
    $maPBDC = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $trangThaiMoi = isset($_GET['status']) ? $_GET['status'] : '';
    
    if ($maPBDC > 0 && !empty($trangThaiMoi)) {
        if ($phanBo->capNhatTrangThai($maPBDC, $trangThaiMoi)) {
            header("Location: pbdc.php?update_success=1");
            exit();
        }
    }
}

// Xử lý thêm phân bổ mới
$thongBao = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['themPhanBo'])) {
    $maDC = isset($_POST['maDC']) ? $_POST['maDC'] : '';
    $maKHSX = isset($_POST['maKHSX']) ? $_POST['maKHSX'] : '';
    $maSP = isset($_POST['maSP']) ? $_POST['maSP'] : '';
    $soLuong = isset($_POST['soLuong']) ? $_POST['soLuong'] : '';
    $ngayBatDau = isset($_POST['ngayBatDau']) ? $_POST['ngayBatDau'] : '';
    $ngayKetThuc = isset($_POST['ngayKetThuc']) ? $_POST['ngayKetThuc'] : '';
    $ghiChu = isset($_POST['ghiChu']) ? $_POST['ghiChu'] : '';
    
    if ($phanBo->themPhanBo($maDC, $maKHSX, $maSP, $soLuong, $ngayBatDau, $ngayKetThuc, $ghiChu)) {
        header("Location: pbdc.php?success=1");
        exit();
    } else {
        $thongBao = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle me-2"></i>Lỗi khi thêm phân bổ dây chuyền!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}

if (isset($_GET['success'])) {
    $thongBao = '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>Thêm phân bổ dây chuyền thành công!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}

if (isset($_GET['update_success'])) {
    $thongBao = '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>Cập nhật trạng thái thành công!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}

// Xử lý tìm kiếm
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$dsPhanBo = $keyword ? $phanBo->timKiemPhanBo($keyword) : $phanBo->layDanhSachPhanBo();
$thongKe = $phanBo->layThongKe();

// Lấy danh sách cho form
$dsDayChuyen = $phanBo->layDanhSachDayChuyen();
$dsKeHoach = $phanBo->layKeHoachChuaPhanBo();

// Debug: Hiển thị thông tin kế hoạch
if (isset($_GET['debug'])) {
    echo "<pre style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; margin: 20px;'>";
    echo "Số lượng kế hoạch tìm thấy: " . count($dsKeHoach) . "\n\n";
    echo "Chi tiết kế hoạch:\n";
    print_r($dsKeHoach);
    echo "</pre>";
}
?>

<div class="content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">
                <i class="bi bi-diagram-3 me-2"></i>LẬP KẾ HOẠCH PHÂN BỔ DÂY CHUYỀN
            </h3>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalThemPhanBo">
                <i class="bi bi-plus-circle me-2"></i>Thêm Phân Bổ Mới
            </button>
        </div>

        <?php echo $thongBao; ?>

        <!-- THỐNG KÊ -->
        <div class="row mb-3">
            <div class="col-lg-4 col-md-4 mb-3">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body text-white text-center">
                        <h2 class="mb-0 fw-bold"><?php echo $thongKe['tongDayChuyen']; ?></h2>
                        <p class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Tổng Số Dây Chuyền</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 mb-3">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <div class="card-body text-white text-center">
                        <h2 class="mb-0 fw-bold"><?php echo $thongKe['tongPhanBo']; ?></h2>
                        <p class="mb-0"><i class="bi bi-check-circle me-2"></i>Tổng Phân Bổ</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 mb-3">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="card-body text-white text-center">
                        <h2 class="mb-0 fw-bold"><?php echo $thongKe['tenXuong']; ?></h2>
                        <p class="mb-0"><i class="bi bi-building me-2"></i>Xưởng Sản Xuất</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- KẾ HOẠCH SẢN XUẤT ĐÃ DUYỆT -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-info text-white fw-bold">
                <i class="bi bi-clipboard-check me-2"></i>Kế Hoạch Sản Xuất Đã Duyệt (<?php echo count($dsKeHoach); ?>)
            </div>
            <div class="card-body">
                <?php if (!empty($dsKeHoach)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã KHSX</th>
                                    <th>Sản Phẩm</th>
                                    <th>Số Lượng</th>
                                    <th>Ngày Lập</th>
                                    <th>Hạn Giao</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dsKeHoach as $kh): ?>
                                <tr>
                                    <td>KHSX-<?php echo $kh['maKHSX']; ?></td>
                                    <td><?php echo htmlspecialchars($kh['tenSP']); ?></td>
                                    <td><?php echo number_format($kh['soLuong'], 0, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($kh['ngayLap'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($kh['ngayGiaoDuKien'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Không có kế hoạch sản xuất nào đã được duyệt. Vui lòng duyệt kế hoạch sản xuất trước khi phân bổ dây chuyền.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TÌM KIẾM -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-search me-2"></i>Tìm Kiếm Theo Mã DC, SP...
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-lg-10 col-md-9">
                            <input type="text" name="keyword" class="form-control" 
                                   placeholder="Nhập mã dây chuyền, tên dây chuyền..."
                                   value="<?php echo htmlspecialchars($keyword); ?>">
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-2"></i>Tìm Kiếm
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- DANH SÁCH DÂY CHUYỀN -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white fw-bold">
                <i class="bi bi-list-ul me-2"></i>Danh Sách Dây Chuyền
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-dark text-center">
                            <tr>
                                <th style="width: 10%;">Mã DC</th>
                                <th style="width: 22%;">Tên Dây Chuyền</th>
                                <th style="width: 18%;">Sản Phẩm</th>
                                <th style="width: 10%;">Số Lượng</th>
                                <th style="width: 12%;">Ngày Bắt Đầu</th>
                                <th style="width: 12%;">Hạn Giao</th>
                                <th style="width: 16%;">Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dsPhanBo)): ?>
                                <?php foreach ($dsPhanBo as $pb): ?>
                                <tr>
                                    <td class="text-center fw-bold">DC<?php echo str_pad($pb['maDC'], 3, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($pb['tenDC']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($pb['tenSP']); ?></td>
                                    <td class="text-end"><?php echo number_format($pb['soLuong'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <?php 
                                        if ($pb['ngayBatDau'] && $pb['ngayBatDau'] != '0000-00-00') {
                                            echo date('d/m/Y', strtotime($pb['ngayBatDau']));
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        if ($pb['ngayKetThuc'] && $pb['ngayKetThuc'] != '0000-00-00') {
                                            echo date('d/m/Y', strtotime($pb['ngayKetThuc']));
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $trangThai = $pb['trangThai'];
                                        $maPBDC = $pb['maPBDC'];
                                        if ($trangThai == 'Hoàn Thành'): 
                                        ?>
                                            <span class="badge bg-success" style="cursor: pointer;" onclick="capNhatTrangThai(<?php echo $maPBDC; ?>, 'Hoàn Thành')" title="Click để thay đổi trạng thái">
                                                <i class="bi bi-check-circle me-1"></i>Hoàn Thành
                                            </span>
                                        <?php elseif ($trangThai == 'Đang thực hiện'): ?>
                                            <span class="badge bg-primary" style="cursor: pointer;" onclick="capNhatTrangThai(<?php echo $maPBDC; ?>, 'Đang thực hiện')" title="Click để thay đổi trạng thái">
                                                <i class="bi bi-arrow-repeat me-1"></i>Đang Thực Hiện
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark" style="cursor: pointer;" onclick="capNhatTrangThai(<?php echo $maPBDC; ?>, 'Chưa bắt đầu')" title="Click để thay đổi trạng thái">
                                                <i class="bi bi-clock me-1"></i>Chưa Bắt Đầu
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                                        <h5>Chưa có phân bổ dây chuyền nào</h5>
                                        <p>Hãy thêm phân bổ sản phẩm cho dây chuyền</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm Phân Bổ -->
<div class="modal fade" id="modalThemPhanBo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Thêm Phân Bổ Dây Chuyền Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Dây Chuyền <span class="text-danger">*</span></label>
                            <select name="maDC" class="form-select" required>
                                <option value="">-- Chọn dây chuyền --</option>
                                <?php foreach ($dsDayChuyen as $dc): ?>
                                    <option value="<?php echo $dc['maDC']; ?>">
                                        DC<?php echo str_pad($dc['maDC'], 3, '0', STR_PAD_LEFT); ?> - <?php echo htmlspecialchars($dc['tenDC']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kế Hoạch Sản Xuất <span class="text-danger">*</span></label>
                            <select name="maKHSX" id="selectKHSX" class="form-select" required>
                                <option value="">-- Chọn kế hoạch --</option>
                                <?php if (!empty($dsKeHoach)): ?>
                                    <?php foreach ($dsKeHoach as $kh): ?>
                                        <option value="<?php echo $kh['maKHSX']; ?>" 
                                                data-masp="<?php echo $kh['maSP']; ?>"
                                                data-tensp="<?php echo htmlspecialchars($kh['tenSP']); ?>"
                                                data-soluong="<?php echo $kh['soLuong']; ?>">
                                            KHSX-<?php echo $kh['maKHSX']; ?> - <?php echo htmlspecialchars($kh['tenSP']); ?> (SL: <?php echo number_format($kh['soLuong'], 0, ',', '.'); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>Không có kế hoạch đã duyệt</option>
                                <?php endif; ?>
                            </select>
                            <?php if (empty($dsKeHoach)): ?>
                                <small class="text-danger">
                                    <i class="bi bi-exclamation-circle me-1"></i>
                                    Chưa có kế hoạch sản xuất nào được duyệt. Vui lòng duyệt kế hoạch trước.
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Sản Phẩm</label>
                            <input type="text" id="displaySP" class="form-control" readonly placeholder="Chọn kế hoạch trước">
                            <input type="hidden" name="maSP" id="inputMaSP">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số Lượng <span class="text-danger">*</span></label>
                            <input type="number" name="soLuong" id="inputSoLuong" class="form-control" required min="1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày Bắt Đầu <span class="text-danger">*</span></label>
                            <input type="date" name="ngayBatDau" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày Kết Thúc <span class="text-danger">*</span></label>
                            <input type="date" name="ngayKetThuc" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Ghi Chú</label>
                            <textarea name="ghiChu" class="form-control" rows="3" placeholder="Nhập ghi chú..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Hủy
                    </button>
                    <button type="submit" name="themPhanBo" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>Thêm Phân Bổ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-fill sản phẩm và số lượng khi chọn kế hoạch
document.getElementById('selectKHSX').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const maSP = selectedOption.getAttribute('data-masp');
    const tenSP = selectedOption.getAttribute('data-tensp');
    const soLuong = selectedOption.getAttribute('data-soluong');
    
    if (maSP) {
        document.getElementById('inputMaSP').value = maSP;
        document.getElementById('displaySP').value = tenSP;
        document.getElementById('inputSoLuong').value = soLuong;
    } else {
        document.getElementById('inputMaSP').value = '';
        document.getElementById('displaySP').value = '';
        document.getElementById('inputSoLuong').value = '';
    }
});

// Validate form trước khi submit
document.querySelector('form[method="POST"]').addEventListener('submit', function(e) {
    const maSP = document.getElementById('inputMaSP').value;
    const soLuong = document.getElementById('inputSoLuong').value;
    
    if (!maSP || maSP === '') {
        e.preventDefault();
        alert('Vui lòng chọn kế hoạch sản xuất!');
        return false;
    }
    
    if (!soLuong || soLuong <= 0) {
        e.preventDefault();
        alert('Vui lòng nhập số lượng hợp lệ!');
        return false;
    }
    
    return true;
});

// Cập nhật trạng thái phân bổ
function capNhatTrangThai(maPBDC, trangThaiHienTai) {
    // Hiển thị menu chọn trạng thái mới
    var trangThaiMoi = '';
    var message = 'Chọn trạng thái mới:\n\n';
    message += '1. Chưa bắt đầu\n';
    message += '2. Đang thực hiện\n';
    message += '3. Hoàn thành\n\n';
    message += 'Trạng thái hiện tại: ' + trangThaiHienTai;
    
    var choice = prompt(message, '');
    
    if (choice === null) return; // User cancelled
    
    switch(choice) {
        case '1':
            trangThaiMoi = 'Chưa bắt đầu';
            break;
        case '2':
            trangThaiMoi = 'Đang thực hiện';
            break;
        case '3':
            trangThaiMoi = 'Hoàn Thành';
            break;
        default:
            alert('Lựa chọn không hợp lệ!');
            return;
    }
    
    if (trangThaiMoi === trangThaiHienTai) {
        alert('Trạng thái mới giống trạng thái hiện tại!');
        return;
    }
    
    if (confirm('Bạn có chắc muốn chuyển trạng thái sang "' + trangThaiMoi + '"?')) {
        window.location.href = 'pbdc.php?action=update_status&id=' + maPBDC + '&status=' + encodeURIComponent(trangThaiMoi);
    }
}
</script>

<?php include_once("../../layout/footer.php"); ?>
