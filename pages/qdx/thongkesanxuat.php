<?php
error_reporting(E_ALL & ~E_NOTICE);
if (!isset($_SESSION)) session_start();

require_once("../../class/clslogin.php");
require_once("../../class/clsTKSX.php");

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

$thongKe = new ThongKeSanXuat();

// Test kết nối và dữ liệu
$testData = $thongKe->testQuery();
error_log("Test data: " . print_r($testData, true));

// Xử lý bộ lọc
$tuNgay = isset($_GET['tu_ngay']) ? $_GET['tu_ngay'] : '2025-10-01';
$denNgay = isset($_GET['den_ngay']) ? $_GET['den_ngay'] : '2025-11-30';
$dayChuyen = isset($_GET['day_chuyen']) ? $_GET['day_chuyen'] : '';
$maSP = isset($_GET['ma_sp']) ? $_GET['ma_sp'] : '';

error_log("Filter: tuNgay={$tuNgay}, denNgay={$denNgay}, maSP={$maSP}");

// Lấy dữ liệu từ CSDL
$dsThongKe = $thongKe->layDuLieuThongKe($tuNgay, $denNgay, $dayChuyen, $maSP);
$dsSanPham = $thongKe->layDanhSachSanPham();
$tongQuan = $thongKe->layTongQuanThongKe($tuNgay, $denNgay);

error_log("dsThongKe count: " . count($dsThongKe));
error_log("dsSanPham count: " . count($dsSanPham));
error_log("tongQuan: " . print_r($tongQuan, true));

// Tính toán
$tyLeHoanThanh = $tongQuan['tongKeHoach'] > 0 ? 
    round(($tongQuan['slThucTe'] / $tongQuan['tongKeHoach']) * 100, 1) : 0;
$tyLeLoi = $tongQuan['slThucTe'] > 0 ? 
    round(($tongQuan['slLoi'] / $tongQuan['slThucTe']) * 100, 3) : 0;
?>

<div class="content">
    <div class="container-fluid">
        <h4 class="mb-4 text-primary">
            <i class="bi bi-graph-up-arrow me-2"></i>THỐNG KÊ SẢN XUẤT
        </h4>
        
        <!-- DEBUG INFO -->
        <?php if (isset($_GET['debug'])): ?>
        <div class="alert alert-info">
            <strong>Debug Info:</strong><br>
            - Số bản ghi thống kê: <?php echo count($dsThongKe); ?><br>
            - Số sản phẩm: <?php echo count($dsSanPham); ?><br>
            - Tổng kế hoạch: <?php echo $tongQuan['tongKeHoach']; ?><br>
            - Khoảng thời gian: <?php echo $tuNgay; ?> đến <?php echo $denNgay; ?><br>
            <?php if (!empty($dsThongKe)): ?>
            - Dữ liệu mẫu: <?php echo print_r($dsThongKe[0], true); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- BỘ LỌC -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-funnel me-2"></i>Tiêu Chí Lọc
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">Từ <span class="text-danger">*</span></label>
                            <input type="date" name="tu_ngay" class="form-control" value="<?php echo htmlspecialchars($tuNgay); ?>" required>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">Đến <span class="text-danger">*</span></label>
                            <input type="date" name="den_ngay" class="form-control" value="<?php echo htmlspecialchars($denNgay); ?>" required>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">Dây Chuyền</label>
                            <select name="day_chuyen" class="form-select">
                                <option value="">Tất Cả Dây Chuyền</option>
                                <option value="DC01" <?php echo $dayChuyen == 'DC01' ? 'selected' : ''; ?>>DC01</option>
                                <option value="DC02" <?php echo $dayChuyen == 'DC02' ? 'selected' : ''; ?>>DC02</option>
                                <option value="DC03" <?php echo $dayChuyen == 'DC03' ? 'selected' : ''; ?>>DC03</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-bold">Sản Phẩm</label>
                            <select name="ma_sp" class="form-select">
                                <option value="">Tất Cả Sản Phẩm</option>
                                <?php foreach ($dsSanPham as $sp): ?>
                                    <option value="<?php echo $sp['maSP']; ?>" <?php echo $maSP == $sp['maSP'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sp['maSP'] . ' - ' . $sp['tenSP']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Xem Dữ Liệu
                        </button>
                        <a href="?" class="btn btn-secondary ms-2">
                            <i class="bi bi-arrow-clockwise me-2"></i>Đặt Lại
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- THỐNG KÊ TỔNG QUAN -->
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body text-white text-center">
                        <h2 class="mb-0 fw-bold"><?php echo number_format($tongQuan['tongKeHoach']); ?></h2>
                        <p class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Tổng Sản Lượng Thực Tế</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <div class="card-body text-white text-center">
                        <h2 class="mb-0 fw-bold"><?php echo $tyLeHoanThanh; ?>%</h2>
                        <p class="mb-0"><i class="bi bi-check-circle me-2"></i>Tỷ Lệ Hoàn Thành Kế Hoạch</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="card-body text-white text-center">
                        <h2 class="mb-0 fw-bold"><?php echo number_format($tongQuan['slLoi']); ?></h2>
                        <p class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Tổng Sản Phẩm Lỗi</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <div class="card-body text-white text-center">
                        <h2 class="mb-0 fw-bold"><?php echo $tyLeLoi; ?>%</h2>
                        <p class="mb-0"><i class="bi bi-percent me-2"></i>Tỷ Lệ Lỗi (PPMOR)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- BẢNG CHI TIẾT -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-table me-2"></i>Bảng Chi Tiết Kết Quả Sản Xuất (Theo Ngày/Ca)</span>
                <button class="btn btn-light btn-sm" onclick="alert('Chức năng xuất Excel đang phát triển')">
                    <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-success text-center">
                            <tr>
                                <th style="width: 5%;">STT</th>
                                <th style="width: 12%;">Thời Gian (Ngày/Ca)</th>
                                <th style="width: 10%;">Dây Chuyền SX</th>
                                <th style="width: 8%;">Mã SP</th>
                                <th style="width: 13%;">Sản Lượng Kế Hoạch</th>
                                <th style="width: 13%;">Sản Lượng Thực Tế</th>
                                <th style="width: 13%;">Tỷ Lệ Hoàn Thành</th>
                                <th style="width: 13%;">Sản Phẩm Lỗi (Đơn vị)</th>
                                <th style="width: 13%;">Tỷ Lệ Lỗi (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dsThongKe)): ?>
                                <?php 
                                $stt = 0;
                                foreach ($dsThongKe as $row): 
                                    $stt++;
                                    $ca = rand(1, 3);
                                ?>
                                <tr>
                                    <td class="text-center fw-bold"><?php echo $stt; ?></td>
                                    <td class="text-center"><?php echo $row['ThoiGian']; ?> (Ca <?php echo $ca; ?>)</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?php echo $row['DayChuyen']; ?></span>
                                    </td>
                                    <td class="text-center fw-bold text-danger"><?php echo $row['MS_SP']; ?></td>
                                    <td class="text-end"><?php echo number_format($row['SL_KeHoach']); ?></td>
                                    <td class="text-end fw-bold text-success"><?php echo number_format($row['SL_ThucTe']); ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $tyLe = $row['TyLeHoanThanh'];
                                        $badgeClass = 'bg-success';
                                        if ($tyLe < 80) $badgeClass = 'bg-danger';
                                        elseif ($tyLe < 90) $badgeClass = 'bg-warning text-dark';
                                        elseif ($tyLe < 100) $badgeClass = 'bg-info';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $tyLe; ?>%</span>
                                    </td>
                                    <td class="text-end text-danger fw-bold"><?php echo number_format($row['SP_Loi']); ?></td>
                                    <td class="text-center">
                                        <?php 
                                        $tyLeLoi = $row['TyLeLoi'];
                                        $badgeClass = 'bg-success';
                                        if ($tyLeLoi > 3) $badgeClass = 'bg-danger';
                                        elseif ($tyLeLoi > 1) $badgeClass = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $tyLeLoi; ?>%</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-light fw-bold">
                                    <td colspan="4" class="text-end">TỔNG CỘNG</td>
                                    <td class="text-end"><?php echo number_format($tongQuan['tongKeHoach']); ?></td>
                                    <td class="text-end text-success"><?php echo number_format($tongQuan['slThucTe']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?php echo $tyLeHoanThanh; ?>%</span>
                                    </td>
                                    <td class="text-end text-danger"><?php echo number_format($tongQuan['slLoi']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark"><?php echo $tyLeLoi; ?>%</span>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 mb-3 d-block text-secondary"></i>
                                        <h5>Không có dữ liệu thống kê</h5>
                                        <p>Vui lòng chọn khoảng thời gian khác hoặc kiểm tra dữ liệu sản xuất.</p>
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

<?php include_once("../../layout/footer.php"); ?>
