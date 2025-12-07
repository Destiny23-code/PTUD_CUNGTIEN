<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION)) {
    session_start();
}

require_once "../../class/clslogin.php";

$p = new login();

$session_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
$session_user = isset($_SESSION['user']) ? $_SESSION['user'] : '';
$session_pass = isset($_SESSION['pass']) ? $_SESSION['pass'] : '';
$session_phanquyen = isset($_SESSION['phanquyen']) ? $_SESSION['phanquyen'] : 0;

if (!$p->confirmlogin($session_id, $session_user, $session_pass, $session_phanquyen) || $session_phanquyen != 2) {
    header("Location: ../dangnhap.php");
    exit();
}

error_reporting(E_ALL & ~E_NOTICE);

include_once '../../layout/giaodien/qdx.php';
require_once "../../class/clsLapPYCNL.php";

$pycnl = new LapPYCNL();

$maPYCNL = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($maPYCNL <= 0) {
    header("Location: dspycnl.php");
    exit();
}

$phieu = $pycnl->getPhieuYeuCauById($maPYCNL);
if (!$phieu) {
    echo "<script>alert('Không tìm thấy phiếu yêu cầu!'); window.location.href='dspycnl.php';</script>";
    exit();
}

$chiTiet = $pycnl->getChiTietPhieuYeuCau($maPYCNL);

$trangThai = $phieu['trangThai'];
$badge_class = 'badge-cho-duyet';
$bg_class = 'bg-warning';
if ($trangThai == 'Đã duyệt') {
    $badge_class = 'badge-da-duyet';
    $bg_class = 'bg-primary';
} elseif ($trangThai == 'Đã cấp') {
    $badge_class = 'badge-da-cap';
    $bg_class = 'bg-success';
} elseif ($trangThai == 'Đã hủy') {
    $badge_class = 'badge-da-huy';
    $bg_class = 'bg-danger';
}
?>

<style>
    .modal-header-custom {
        background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .info-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #dee2e6;
        flex-wrap: wrap;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        color: #495057;
        min-width: 200px;
    }
    .info-value {
        color: #212529;
        text-align: right;
        flex: 1;
    }
    .badge-da-huy { 
        background-color: #dc3545; 
        color: white; 
        padding: 8px 16px; 
        border-radius: 6px; 
        display: inline-block;
        font-weight: 500;
    }
    .badge-da-duyet { 
        background-color: #0d6efd; 
        color: white; 
        padding: 8px 16px; 
        border-radius: 6px; 
        display: inline-block;
        font-weight: 500;
    }
    .badge-cho-duyet { 
        background-color: #ffc107; 
        color: black; 
        padding: 8px 16px; 
        border-radius: 6px; 
        display: inline-block;
        font-weight: 500;
    }
    .badge-da-cap { 
        background-color: #198754; 
        color: white; 
        padding: 8px 16px; 
        border-radius: 6px; 
        display: inline-block;
        font-weight: 500;
    }
    .detail-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .detail-table thead {
        background: #1976d2;
        color: white;
    }
    .detail-table thead th {
        font-weight: 600;
        padding: 15px;
    }
    .detail-table tbody td {
        padding: 12px 15px;
    }
    .btn-close-custom {
        background: rgba(255,255,255,0.2);
        border: 2px solid white;
        border-radius: 50%;
        padding: 8px 12px;
        color: white;
        transition: all 0.3s;
    }
    .btn-close-custom:hover {
        background: white;
        color: #1976d2;
        transform: rotate(90deg);
    }
    
    .btn-lg {
        padding: 12px 30px;
        font-size: 16px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .btn-lg:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    @media (max-width: 768px) {
        .info-row {
            flex-direction: column;
        }
        .info-label, .info-value {
            text-align: left;
            min-width: 100%;
        }
        .btn-lg {
            width: 100%;
            margin-bottom: 10px;
        }
        .btn-lg.ms-2 {
            margin-left: 0 !important;
        }
    }
</style>

<div class="content">
    <div class="container-fluid">
        <div class="modal-header-custom p-4 mb-4 position-relative">
            <div class="d-flex justify-content-between align-items-center w-100">
                <div>
                    <h4 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>Chi Tiết Phiếu Yêu Cầu Nguyên Liệu: <?php echo htmlspecialchars($phieu['maPhieu']); ?>
                    </h4>
                </div>
                <button onclick="window.location.href='dspycnl.php'" class="btn btn-close-custom" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%);">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="info-section">
                    <h6 class="text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Thông Tin Chung</h6>
                    <div class="info-row">
                        <span class="info-label">Mã Kế Hoạch Sản Xuất:</span>
                        <span class="info-value">
                            <?php 
                            if (!empty($phieu['tenSP'])) {
                                echo htmlspecialchars($phieu['tenSP']) . ' (KH' . htmlspecialchars($phieu['maKHSX']) . ')';
                            } else {
                                echo 'KH' . htmlspecialchars($phieu['maKHSX']);
                            }
                            ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Xưởng:</span>
                        <span class="info-value"><?php echo htmlspecialchars(!empty($phieu['tenXuong']) ? $phieu['tenXuong'] : 'Chưa xác định'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Người Lập:</span>
                        <span class="info-value">
                            <?php 
                            $nguoiLap = !empty($phieu['tenNguoiLap']) ? $phieu['tenNguoiLap'] : $phieu['nguoiLap'];
                            echo htmlspecialchars($nguoiLap) . ' | Ngày Yêu Cầu: ' . date('Y-m-d', strtotime($phieu['ngayLap'])); 
                            ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Trạng Thái:</span>
                        <span class="<?php echo $badge_class; ?>"><?php echo htmlspecialchars($trangThai); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-section">
                    <h6 class="text-primary mb-3"><i class="bi bi-clipboard-check me-2"></i>Ghi Chú Yêu Cầu</h6>
                    <div class="info-row">
                        <span class="info-value"><?php echo nl2br(htmlspecialchars(!empty($phieu['ghiChu']) ? $phieu['ghiChu'] : 'Không có ghi chú')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-table mt-4">
            <div class="bg-primary text-white p-3 d-flex align-items-center">
                <i class="bi bi-list-check me-2"></i>
                <h6 class="mb-0">Chi Tiết Nguyên Liệu Yêu Cầu</h6>
            </div>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Mã NL</th>
                        <th>Tên Nguyên Liệu</th>
                        <th>Đơn Vị</th>
                        <th>Định Mức Cần</th>
                        <th>Số Lượng Yêu Cầu</th>
                        <th>Vị Trí Kho</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($chiTiet)) {
                        $viTriKho = array('Kệ A1', 'Kệ A2', 'Kệ A3', 'Kệ B1', 'Kệ B2');
                        $index = 0;
                        foreach ($chiTiet as $item) {
                            $soLuongYC = floatval($item['soLuongYeuCau']);
                            $tonKho = floatval($item['soLuongTon']);
                            $viTri = $viTriKho[$index % count($viTriKho)];
                            $index++;
                    ?>
                    <tr>
                        <td class="fw-bold"><?php echo htmlspecialchars($item['maNL']); ?></td>
                        <td><?php echo htmlspecialchars($item['tenNL']); ?></td>
                        <td><?php echo htmlspecialchars($item['donViTinh']); ?></td>
                        <td class="text-end"><?php echo number_format($soLuongYC, 0, ',', '.'); ?></td>
                        <td class="text-end fw-bold text-primary"><?php echo number_format($soLuongYC, 0, ',', '.'); ?></td>
                        <td><?php echo $viTri; ?></td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Không có chi tiết nguyên liệu.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4 mb-4">
            <?php if ($trangThai == 'Chờ duyệt'): ?>
            <button class="btn btn-danger btn-lg me-2" onclick="if(confirm('Bạn có chắc muốn hủy phiếu này?')) { window.location.href='xuly_pycnl.php?action=cancel&id=<?php echo $maPYCNL; ?>'; }">
                <i class="bi bi-x-circle me-2"></i>Hủy Phiếu
            </button>
            <button class="btn btn-success btn-lg" onclick="if(confirm('Bạn có chắc muốn duyệt phiếu này?')) { window.location.href='xuly_pycnl.php?action=approve&id=<?php echo $maPYCNL; ?>'; }">
                <i class="bi bi-check-circle me-2"></i>Duyệt Phiếu
            </button>
            <?php elseif ($trangThai == 'Đã duyệt'): ?>
            <button class="btn btn-primary btn-lg" onclick="if(confirm('Xác nhận đã cấp nguyên liệu cho phiếu này?')) { window.location.href='xuly_pycnl.php?action=supply&id=<?php echo $maPYCNL; ?>'; }">
                <i class="bi bi-box-seam me-2"></i>Đã Cấp Nguyên Liệu
            </button>
            <?php endif; ?>
            
            <button class="btn btn-secondary btn-lg <?php echo ($trangThai == 'Chờ duyệt' || $trangThai == 'Đã duyệt') ? 'ms-2' : ''; ?>" onclick="window.location.href='dspycnl.php'">
                <i class="bi bi-arrow-left me-2"></i>Đóng
            </button>
        </div>
    </div>
</div>

<?php include_once "../../layout/footer.php"; ?>
