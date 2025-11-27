<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();

require_once("../../class/clslogin.php");
require_once("../../class/clsPhanCongNC.php");

$p = new login();
$session_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
$session_user = isset($_SESSION['user']) ? $_SESSION['user'] : '';
$session_pass = isset($_SESSION['pass']) ? $_SESSION['pass'] : '';
$session_phanquyen = isset($_SESSION['phanquyen']) ? $_SESSION['phanquyen'] : 0;

if (!$p->confirmlogin($session_id, $session_user, $session_pass, $session_phanquyen) || $session_phanquyen != 2) {
    header("Location: ../dangnhap.php");
    exit();
}

$phanCong = new PhanCongNhanCong();

// Lấy thông tin nhân viên đang đăng nhập
$maNVDangNhap = $_SESSION['maNV']; // Lấy mã nhân viên từ session
$maDCCuaToi = $phanCong->layDayChuyenCuaNhanVien($maNVDangNhap);
$maXuongCuaToi = $phanCong->layXuongCuaXuongTruong($maNVDangNhap);
$thongTinDC = $maDCCuaToi ? $phanCong->layThongTinDayChuyen($maDCCuaToi) : null;

// XỬ LÝ AJAX - Lấy nhân viên theo dây chuyền cụ thể
if (isset($_GET['ajax']) && $_GET['ajax'] == 'getNhanVien' && isset($_GET['maDC'])) {
    header('Content-Type: application/json');
    $maDC = intval($_GET['maDC']);
    
    // Lấy CHỈ nhân viên thuộc dây chuyền này (maDC)
    $dsNV = $phanCong->layDanhSachNhanVien($maDC, null);
    
    echo json_encode($dsNV);
    exit();
}

include_once('../../layout/giaodien/qdx.php');

$msg = "";
$msgType = "danger";

// Xử lý thêm phân công
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnPhanCong'])) {
    $maDC = isset($_POST['maDC']) ? intval($_POST['maDC']) : 0;
    $maNV = isset($_POST['maNV']) ? intval($_POST['maNV']) : 0;
    $ngayLamViec = isset($_POST['ngayLamViec']) ? $_POST['ngayLamViec'] : '';
    $gioBatDau = isset($_POST['gioBatDau']) ? $_POST['gioBatDau'] : '';
    $gioKetThuc = isset($_POST['gioKetThuc']) ? $_POST['gioKetThuc'] : '';
    $ghiChu = isset($_POST['ghiChu']) ? trim($_POST['ghiChu']) : '';
    
    if ($maDC > 0 && $maNV > 0 && $ngayLamViec && $gioBatDau && $gioKetThuc) {
        $result = $phanCong->themPhanCong($maDC, $maNV, $ngayLamViec, $gioBatDau, $gioKetThuc, $ghiChu, $maXuongCuaToi);
        if ($result === true) {
            $msg = "Phân công nhân công thành công!";
            $msgType = "success";
        } else {
            $msg = $result;
            $msgType = "danger";
        }
    } else {
        $msg = "Vui lòng điền đầy đủ thông tin!";
        $msgType = "warning";
    }
}

// Xử lý xóa phân công
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $result = $phanCong->xoaPhanCong(intval($_GET['id']));
    if ($result === true) {
        $msg = "Xóa phân công thành công!";
        $msgType = "success";
    } else {
        $msg = $result;
        $msgType = "danger";
    }
}

// Lấy dữ liệu
$ngayLoc = isset($_GET['ngay']) ? $_GET['ngay'] : date('Y-m-d');
$maDCLoc = isset($_GET['maDC']) ? intval($_GET['maDC']) : 0;
$maXuongLoc = isset($_GET['maXuong']) ? intval($_GET['maXuong']) : 0;

// Lấy danh sách xưởng (CHỈ xưởng của quản đốc)
$dsXuong = $phanCong->layDanhSachXuong($maXuongCuaToi);

// Lấy CHỈ dây chuyền thuộc xưởng của quản đốc
$dsDayChuyen = $phanCong->layDanhSachDayChuyen(null, $maXuongCuaToi);
// Lấy phân công CHỈ trong xưởng của quản đốc
$dsPhanCong = $phanCong->layDanhSachPhanCong($ngayLoc, $maDCLoc, $maXuongCuaToi);
?>

<div class="content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">
                <i class="bi bi-people me-2"></i>PHÂN CÔNG NHÂN CÔNG
            </h3>
            <?php if ($thongTinDC): ?>
                <div class="alert alert-info mb-0 py-2">
                    <i class="bi bi-building me-1"></i>
                    <strong>Xưởng của bạn:</strong> <?php echo htmlspecialchars($thongTinDC['tenXuong']); ?>
                    <br>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Quản lý <?php echo count($dsDayChuyen); ?> dây chuyền trong xưởng này
                    </small>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show">
                <i class="bi bi-<?php echo $msgType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>-fill me-2"></i>
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- FORM PHÂN CÔNG -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-info text-white fw-bold">
                <i class="bi bi-person-plus me-2"></i>Thông Tin Phân Công
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="bi bi-gear me-1"></i>Dây Chuyền <span class="text-danger">*</span>
                            </label>
                            <select name="maDC" id="selectDayChuyen" class="form-select" required>
                                <option value="">-- Chọn Dây Chuyền --</option>
                                <?php foreach ($dsDayChuyen as $dc): ?>
                                    <option value="<?php echo $dc['maDC']; ?>">
                                        <?php echo htmlspecialchars($dc['tenDC']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Bước 1: Chọn dây chuyền trong xưởng của bạn</small>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person me-1"></i>Nhân Viên <span class="text-danger">*</span>
                            </label>
                            <select name="maNV" id="selectNhanVien" class="form-select" required disabled>
                                <option value="">-- Chọn dây chuyền trước --</option>
                            </select>
                            <small class="text-muted" id="loadingNV" style="display:none;">
                                <i class="bi bi-arrow-repeat"></i> Đang tải...
                            </small>
                            <small class="text-success" id="hintNV">
                                <i class="bi bi-info-circle me-1"></i>
                                Bước 2: Chọn nhân viên (tất cả nhân viên trong xưởng)
                            </small>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="bi bi-calendar me-1"></i>Ngày Làm Việc <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="ngayLamViec" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="bi bi-clock me-1"></i>Giờ Bắt Đầu <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="gioBatDau" class="form-control" value="06:00" required>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="bi bi-clock-fill me-1"></i>Giờ Kết Thúc <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="gioKetThuc" class="form-control" value="18:00" required>
                        </div>
                        <div class="col-lg-6 col-md-12 d-flex align-items-end">
                            <button type="submit" name="btnPhanCong" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-check-circle me-2"></i>Phân Công Nhân Công
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- BỘ LỌC -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-success text-white fw-bold">
                <i class="bi bi-funnel me-2"></i>Bộ Lọc Danh Sách Phân Công
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-lg-5 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="bi bi-calendar me-1"></i>Lọc Theo Ngày
                            </label>
                            <input type="date" name="ngay" class="form-control" value="<?php echo htmlspecialchars($ngayLoc); ?>">
                        </div>
                        <div class="col-lg-5 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="bi bi-gear me-1"></i>Dây Chuyền
                            </label>
                            <select name="maDC" class="form-select">
                                <option value="0">Tất Cả Dây Chuyền</option>
                                <?php foreach ($dsDayChuyen as $dc): ?>
                                    <option value="<?php echo $dc['maDC']; ?>" <?php echo $maDCLoc == $dc['maDC'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dc['tenDC']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-12 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-2"></i>Lọc
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- BẢNG PHÂN CÔNG -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-primary text-center">
                            <tr>
                                <th style="width: 12%;">Ngày</th>
                                <th style="width: 30%;">Dây Chuyền</th>
                                <th style="width: 25%;">Nhân Viên</th>
                                <th style="width: 12%;">Giờ BĐ</th>
                                <th style="width: 12%;">Giờ KT</th>
                                <th style="width: 9%;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dsPhanCong)): ?>
                                <?php foreach ($dsPhanCong as $pc): ?>
                                <tr>
                                    <td class="text-center"><?php echo $pc['ngayFormat']; ?></td>
                                    <td>
                                        <i class="bi bi-gear text-success me-1"></i>
                                        <?php echo htmlspecialchars($pc['tenDC']); ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-person text-info me-1"></i>
                                        <?php echo htmlspecialchars($pc['tenNV']); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($pc['tenLoai']); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?php echo $pc['gioBDFormat']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger"><?php echo $pc['gioKTFormat']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="?action=delete&id=<?php echo $pc['maPC']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Xóa phân công này?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                                        <h5>Chưa có phân công nào</h5>
                                        <p>Vui lòng thêm phân công mới ở form trên.</p>
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

<script>
// AJAX - Load nhân viên khi chọn dây chuyền
document.getElementById('selectDayChuyen').addEventListener('change', function() {
    const maDC = this.value;
    const selectNV = document.getElementById('selectNhanVien');
    const loadingNV = document.getElementById('loadingNV');
    const hintNV = document.getElementById('hintNV');
    
    if (!maDC) {
        selectNV.disabled = true;
        selectNV.innerHTML = '<option value="">-- Chọn dây chuyền trước --</option>';
        return;
    }
    
    // Hiển thị loading
    loadingNV.style.display = 'inline';
    hintNV.style.display = 'none';
    selectNV.disabled = true;
    selectNV.innerHTML = '<option value="">Đang tải...</option>';
    
    // Gọi AJAX
    fetch('?ajax=getNhanVien&maDC=' + maDC)
        .then(response => response.json())
        .then(data => {
            loadingNV.style.display = 'none';
            hintNV.style.display = 'inline';
            
            if (data && data.length > 0) {
                selectNV.innerHTML = '<option value="">-- Chọn Nhân Viên --</option>';
                data.forEach(nv => {
                    const option = document.createElement('option');
                    option.value = nv.maNV;
                    option.textContent = nv.tenNV + ' - ' + nv.tenLoai;
                    selectNV.appendChild(option);
                });
                selectNV.disabled = false;
            } else {
                selectNV.innerHTML = '<option value="">Không có nhân viên trong dây chuyền này</option>';
                selectNV.disabled = true;
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            loadingNV.style.display = 'none';
            hintNV.style.display = 'inline';
            selectNV.innerHTML = '<option value="">Lỗi khi tải dữ liệu</option>';
            selectNV.disabled = true;
        });
});
</script>

<?php include_once("../../layout/footer.php"); ?>
