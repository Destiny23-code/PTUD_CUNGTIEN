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

// Xử lý thêm phân bổ mới
$thongBao = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['themPhanBo'])) {
    $maDC = $_POST['maDC'];
    $maKHSX = $_POST['maKHSX'];
    $maSP = $_POST['maSP'];
    $soLuong = $_POST['soLuong'];
    $ngayBatDau = $_POST['ngayBatDau'];
    $ngayKetThuc = $_POST['ngayKetThuc'];
    $ghiChu = $_POST['ghiChu'];
    
    if ($phanBo->themPhanBo($maDC, $maKHSX, $maSP, $soLuong, $ngayBatDau, $ngayKetThuc, $ghiChu)) {
        $thongBao = '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>Thêm phân bổ dây chuyền thành công!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    } else {
        $thongBao = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle me-2"></i>Lỗi khi thêm phân bổ dây chuyền!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}

// Xử lý tìm kiếm
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$dsPhanBo = $keyword ? $phanBo->timKiemPhanBo($keyword) : $phanBo->layDanhSachPhanBo();
$thongKe = $phanBo->layThongKe();

// Lấy danh sách cho form
$dsDayChuyen = $phanBo->layDanhSachDayChuyen();
$dsKeHoach = $phanBo->layKeHoachChuaPhanBo();
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
            <div class="col-lg-6 col-md-6 mb-3">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body text-white text-center">
                        <h2 class="mb-0 fw-bold"><?php echo $thongKe['tongDayChuyen']; ?></h2>
                        <p class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Tổng Số Dây Chuyền</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 mb-3">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <div class="card-body text-white text-center">
                        <h2 class="mb-0 fw-bold"><?php echo $thongKe['tongHoatDong']; ?></h2>
                        <p class="mb-0"><i class="bi bi-check-circle me-2"></i>Dây Chuyền Hoạt Động</p>
                    </div>
                </div>
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
                                <th style="width: 15%;">Mã DC</th>
                                <th style="width: 30%;">Tên Dây Chuyền</th>
                                <th style="width: 20%;">Sản Phẩm</th>
                                <th style="width: 15%;">Số Lượng</th>
                                <th style="width: 10%;">Hạn Giao</th>
                                <th style="width: 10%;">Hành Động</th>
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
                                        if ($pb['ngayKetThuc'] && $pb['ngayKetThuc'] != '0000-00-00') {
                                            echo date('Y-m-d', strtotime($pb['ngayKetThuc']));
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        $trangThai = $pb['trangThai'];
                                        if ($trangThai == 'Hoàn Thành'): 
                                        ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Hoàn Thành
                                            </span>
                                        <?php elseif ($trangThai == 'Đang thực hiện'): ?>
                                            <span class="badge bg-primary">
                                                <i class="bi bi-arrow-repeat me-1"></i>Đang Chạy
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-clock me-1"></i>Chưa Chọn
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                                        <h5>Không tìm thấy phân bổ dây chuyền nào</h5>
                                        <p>Vui lòng chạy file SQL <strong>tao_bang_phanbodaychuyen.sql</strong> để tạo bảng và thêm dữ liệu mẫu.</p>
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
                                <?php foreach ($dsKeHoach as $kh): ?>
                                    <option value="<?php echo $kh['maKHSX']; ?>" 
                                            data-masp="<?php echo $kh['maSP']; ?>"
                                            data-tensp="<?php echo htmlspecialchars($kh['tenSP']); ?>"
                                            data-soluong="<?php echo $kh['soLuong']; ?>">
                                        KHSX-<?php echo $kh['maKHSX']; ?> (<?php echo htmlspecialchars($kh['tenSP']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
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
</script>

<?php include_once("../../layout/footer.php"); ?>
