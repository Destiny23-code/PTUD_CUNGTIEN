<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kiểm tra session và quyền truy cập
if (!isset($_SESSION)) {
    session_start();
}

require_once("../../class/clslogin.php");

$p = new login();

// Kiểm tra session
$session_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
$session_user = isset($_SESSION['user']) ? $_SESSION['user'] : '';
$session_pass = isset($_SESSION['pass']) ? $_SESSION['pass'] : '';
$session_phanquyen = isset($_SESSION['phanquyen']) ? $_SESSION['phanquyen'] : 0;

if (!$p->confirmlogin($session_id, $session_user, $session_pass, $session_phanquyen) || $session_phanquyen != 2) {
    header("Location: ../dangnhap.php");
    exit();
}

// Tắt hiển thị notice
error_reporting(E_ALL & ~E_NOTICE);

include_once('../../layout/giaodien/qdx.php');
require_once("../../class/clsLapPYCNL.php");

$pycnl = new LapPYCNL();
$msg = "";

// Lấy tham số
$maKHSX_Selected = 0;
$maSP_Selected = 0;
$maPYCNL_Edit = 0;
$phieuEdit = null;

// Kiểm tra nếu đang xem/sửa phiếu
if (isset($_GET['id'])) {
    $maPYCNL_Edit = intval($_GET['id']);
    if ($maPYCNL_Edit > 0) {
        $phieuEdit = $pycnl->getPhieuYeuCauById($maPYCNL_Edit);
        if ($phieuEdit) {
            $maKHSX_Selected = intval($phieuEdit['maKHSX']);
            // Lấy maSP từ kế hoạch
            $thongTinKHSX_temp = $pycnl->getKeHoachSanXuatById($maKHSX_Selected);
        }
    }
}

if (isset($_POST['keHoachSX'])) {
    $parts = explode('_', $_POST['keHoachSX']);
    $maKHSX_Selected = intval($parts[0]);
    $maSP_Selected = isset($parts[1]) ? intval($parts[1]) : 0;
} elseif (isset($_GET['maKHSX'])) {
    $maKHSX_Selected = intval($_GET['maKHSX']);
    $maSP_Selected = isset($_GET['maSP']) ? intval($_GET['maSP']) : 0;
}

// Lấy dữ liệu từ CSDL
$currentDate = isset($_POST['ngayYeuCau']) ? $_POST['ngayYeuCau'] : date("d/m/Y");
$maNguoiLap = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

// Lấy thông tin nhân viên để hiển thị họ tên
$thongTinNV = null;
$nguoiLap = 'Trần Thị Hạnh'; // Mặc định là Trần Thị Hạnh
if ($maNguoiLap > 0) {
    $thongTinNV = $pycnl->getNhanVienById($maNguoiLap);
    if ($thongTinNV) {
        $nguoiLap = $thongTinNV['tenNV'];
    }
}

// Cho phép chỉnh sửa tên người lập nếu có POST
if (isset($_POST['nguoiLap']) && !empty($_POST['nguoiLap'])) {
    $nguoiLap = $_POST['nguoiLap'];
}

// Lấy xưởng của nhân viên đang đăng nhập
$maXuong = 0;
$thongTinXuong = null;
$tenXuong = '';
$danhSachXuong = array();

if ($maNguoiLap > 0) {
    $thongTinNV = $pycnl->getNhanVienById($maNguoiLap);
    if ($thongTinNV && isset($thongTinNV['maDC'])) {
        // Lấy xưởng từ dây chuyền của nhân viên
        $maDC = $thongTinNV['maDC'];
        $xuongCuaNV = $pycnl->getXuongByDayChuyenId($maDC);
        if ($xuongCuaNV) {
            $maXuong = intval($xuongCuaNV['maXuong']);
            $thongTinXuong = $xuongCuaNV;
            $tenXuong = $xuongCuaNV['tenXuong'];
            $danhSachXuong = array($xuongCuaNV); // Chỉ có 1 xưởng
        }
    }
}

// Nếu không tìm được xưởng, mặc định là xưởng 4
if ($maXuong == 0) {
    $maXuong = 4;
    $thongTinXuong = $pycnl->getXuongById($maXuong);
    $tenXuong = $thongTinXuong ? $thongTinXuong['tenXuong'] : '';
    $danhSachXuong = $thongTinXuong ? array($thongTinXuong) : array();
}

$keHoachSanXuat = $pycnl->getKeHoachSanXuat();
$nguyenLieuChung = $pycnl->getNguyenLieu();
$dinhMucByKHSX = array();
$thongTinKHSX = null;

// Xử lý khi có KHSX được chọn
if ($maKHSX_Selected > 0 && $maSP_Selected > 0) {
    $dinhMucByKHSX = $pycnl->getDinhMucNguyenLieuByMaSP($maSP_Selected);
    $thongTinKHSX = $pycnl->getKeHoachSanXuatByMaSP($maKHSX_Selected, $maSP_Selected);
}

// Xử lý submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnGuiYeuCau'])) {
    $details = array();
    $maNguyenLieuArr = isset($_POST['maNguyenLieu']) ? $_POST['maNguyenLieu'] : array();
    $soLuongYeuCauArr = isset($_POST['soLuongYeuCau']) ? $_POST['soLuongYeuCau'] : array();
    
    if (is_array($maNguyenLieuArr)) {
        foreach ($maNguyenLieuArr as $i => $maNL) {
            if (!empty($maNL) && isset($soLuongYeuCauArr[$i]) && !empty($soLuongYeuCauArr[$i])) {
                $details[] = array(
                    'maNL' => intval($maNL),
                    'soLuongYeuCau' => $soLuongYeuCauArr[$i]
                );
            }
        }
    }
    
    $ghiChu = isset($_POST['ghiChu']) ? trim($_POST['ghiChu']) : '';
    $maPhieuCustom = isset($_POST['maYeuCau']) ? trim($_POST['maYeuCau']) : '';
    $nguoiLapCustom = isset($_POST['nguoiLap']) ? trim($_POST['nguoiLap']) : '';
    
    if ($maXuong <= 0) {
        $msg = "Vui lòng chọn Xưởng.";
    } elseif ($maKHSX_Selected <= 0) {
        $msg = "Vui lòng chọn Kế hoạch Sản Xuất.";
    } elseif (empty($details)) {
        $msg = "Vui lòng nhập chi tiết nguyên liệu cần yêu cầu.";
    } elseif (empty($nguoiLapCustom)) {
        $msg = "Vui lòng nhập tên người lập phiếu.";
    } else {
        $result = $pycnl->insertPhieuYeuCau($maKHSX_Selected, $maNguoiLap, $maXuong, $details, $ghiChu, $maPhieuCustom, $maSP_Selected);
        
        if ($result === true) {
            // Xóa session đã chọn
            unset($_SESSION['maXuong_selected']);
            echo "<script>alert('Lập phiếu yêu cầu nguyên liệu thành công!'); window.location.href='dspycnl.php';</script>";
            exit();
        } else {
            $msg = "Có lỗi xảy ra: " . htmlspecialchars($result);
        }
    }
}

$maYeuCau_TuDong = "YE" . date("YmdHis");
?>

<div class="content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>LẬP PHIẾU YÊU CẦU NGUYÊN LIỆU
            </h3>
            <a href="dspycnl.php" class="btn btn-outline-primary">
                <i class="bi bi-list-ul me-2"></i>Danh Sách Phiếu
            </a>
        </div>
        
        <?php if (!empty($msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Lỗi!</strong> <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

                <form method="POST" action="pycnl.php" id="formYeuCauNL">
                    <!-- THÔNG TIN PHIẾU -->
                    <div class="card shadow-sm mb-3 border-info">
                        <div class="card-header bg-info text-white fw-bold">
                            <i class="bi bi-file-earmark-text me-2"></i> Thông Tin Phiếu Yêu Cầu
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label fw-bold">Mã Yêu Cầu</label>
                                    <input type="text" class="form-control" name="maYeuCau" value="<?php echo $maYeuCau_TuDong; ?>" placeholder="Nhập mã yêu cầu">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Mã tự động: <?php echo $maYeuCau_TuDong; ?>
                                    </small>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label fw-bold">Ngày Yêu Cầu <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="ngayYeuCau" value="<?php echo $currentDate; ?>" placeholder="dd/mm/yyyy">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label fw-bold">Người Lập Phiếu</label>
                                    <input type="text" class="form-control" name="nguoiLap" value="<?php echo htmlspecialchars($nguoiLap); ?>" placeholder="Nhập tên người lập">
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label fw-bold">Trạng Thái</label>
                                    <?php 
                                    $trangThai = 'Chờ duyệt';
                                    $bgClass = 'bg-warning text-dark';
                                    
                                    if ($phieuEdit) {
                                        $trangThai = $phieuEdit['trangThai'];
                                        switch ($trangThai) {
                                            case 'Đã duyệt':
                                                $bgClass = 'bg-info text-white';
                                                break;
                                            case 'Đã cấp':
                                                $bgClass = 'bg-success text-white';
                                                break;
                                            case 'Đã hủy':
                                                $bgClass = 'bg-danger text-white';
                                                break;
                                            default:
                                                $bgClass = 'bg-warning text-dark';
                                        }
                                    }
                                    ?>
                                    <input type="text" class="form-control <?php echo $bgClass; ?> fw-bold text-center" value="<?php echo htmlspecialchars($trangThai); ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CHỌN XƯỞNG VÀ KẾ HOẠCH -->
                    <div class="card shadow-sm mb-3 border-success">
                        <div class="card-header bg-success text-white fw-bold">
                            <i class="bi bi-clipboard-check me-2"></i> Thông Tin Kế Hoạch Sản Xuất
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-4 col-md-6">
                                    <label class="form-label fw-bold">Xưởng <span class="text-danger">*</span></label>
                                    <select class="form-select" id="maXuong" name="maXuong" required>
                                        <?php if (!empty($danhSachXuong)): ?>
                                            <?php foreach ($danhSachXuong as $xuong): ?>
                                                <option value="<?php echo $xuong['maXuong']; ?>" 
                                                        <?php echo ($maXuong == $xuong['maXuong']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($xuong['tenXuong']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">-- Không có xưởng nào --</option>
                                        <?php endif; ?>
                                    </select>
                                    <?php if ($thongTinXuong && !empty($thongTinXuong['diaChi'])): ?>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            <?php echo htmlspecialchars($thongTinXuong['diaChi']); ?>
                                        </small>
                                    <?php endif; ?>
                                    <small class="text-info d-block mt-1">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Xưởng của bạn: Xưởng Chiết rót & Tiệt trùng chai
                                    </small>
                                </div>
                                <div class="col-lg-8 col-md-6">
                                    <label class="form-label fw-bold">Kế Hoạch Sản Xuất <span class="text-danger">*</span></label>
                                    <select class="form-select" id="keHoachSX" name="keHoachSX" required onchange="this.form.submit()">
                                        <option value="">-- Chọn Kế Hoạch Đã Duyệt --</option>
                                        <?php if (!empty($keHoachSanXuat)): ?>
                                            <?php foreach ($keHoachSanXuat as $kh): ?>
                                                <?php 
                                                $tenSP = isset($kh['tenSP']) ? $kh['tenSP'] : 'Chưa có tên SP';
                                                $soLuong = isset($kh['soLuongCanSX']) ? number_format($kh['soLuongCanSX'], 0, ',', '.') : '0';
                                                $maKH = isset($kh['maKHSX']) ? $kh['maKHSX'] : '';
                                                $maSP = isset($kh['maSP']) ? $kh['maSP'] : '';
                                                $ngayLap = isset($kh['ngayLap']) ? date('d/m/Y', strtotime($kh['ngayLap'])) : '';
                                                $valueOption = $maKH . '_' . $maSP;
                                                $selectedValue = $maKHSX_Selected . '_' . $maSP_Selected;
                                                ?>
                                                <option value="<?php echo $valueOption; ?>" <?php echo ($selectedValue == $valueOption) ? 'selected' : ''; ?>>
                                                    KHSX-<?php echo $maKH; ?> | <?php echo htmlspecialchars($tenSP); ?> | SL: <?php echo $soLuong; ?> | Ngày: <?php echo $ngayLap; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">-- KHÔNG CÓ KẾ HOẠCH NÀO --</option>
                                        <?php endif; ?>
                                    </select>
                                    <?php if (empty($keHoachSanXuat)): ?>
                                        <small class="text-danger">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            Không có kế hoạch sản xuất nào được duyệt trong hệ thống.
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($thongTinKHSX): ?>
                            <div class="row g-3 mt-2">
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong><i class="bi bi-info-circle me-2"></i>Thông tin kế hoạch:</strong>
                                                <ul class="mb-0 mt-2">
                                                    <li>Số lượng cần sản xuất: <strong class="text-primary"><?php echo number_format($thongTinKHSX['soLuongCanSX'], 0, ',', '.'); ?></strong></li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="mb-0 mt-2" style="list-style: none; padding-left: 0;">
                                                    <li><i class="bi bi-calendar-check me-2"></i>Ngày lập: <strong><?php echo date('d/m/Y', strtotime($thongTinKHSX['ngayLap'])); ?></strong></li>
                                                    <?php if (!empty($thongTinKHSX['ngayGiaoDuKien']) && $thongTinKHSX['ngayGiaoDuKien'] != '0000-00-00'): ?>
                                                    <li><i class="bi bi-truck me-2"></i>Ngày giao dự kiến: <strong><?php echo date('d/m/Y', strtotime($thongTinKHSX['ngayGiaoDuKien'])); ?></strong></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- CHI TIẾT NGUYÊN LIỆU YÊU CẦU -->
                    <div class="card shadow-sm mb-3 border-primary">
                        <div class="card-header bg-primary text-white fw-bold">
                            <i class="bi bi-box-seam me-2"></i> Chi Tiết Nguyên Liệu Yêu Cầu
                        </div>
                        <div class="card-body p-0">
                            <?php if ($maKHSX_Selected > 0 && !empty($dinhMucByKHSX)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0">
                                        <thead class="table-primary text-center">
                                            <tr>
                                                <th style="width: 5%;">STT</th>
                                                <th style="width: 8%;">Mã NL</th>
                                                <th style="width: 25%;">Tên Nguyên Liệu</th>
                                                <th style="width: 10%;">Đơn Vị</th>
                                                <th style="width: 13%;">Định Mức<br><small>(1 SP)</small></th>
                                                <th style="width: 13%;">Tồn Kho<br><small>(Hiện tại)</small></th>
                                                <th style="width: 13%;">Tổng Cần<br><small>(Dự tính)</small></th>
                                                <th style="width: 13%;">Số Lượng<br>Yêu Cầu <span class="text-danger">*</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $stt = 0;
                                            $tongThieuHut = 0;
                                            foreach ($dinhMucByKHSX as $item): 
                                                $stt++;
                                                $tonKho = isset($item['tonKhoHienTai']) ? floatval($item['tonKhoHienTai']) : 0;
                                                $dinhMuc = isset($item['soLuongTrong1SP']) ? floatval($item['soLuongTrong1SP']) : 0;
                                                $soLuongCanSX = $thongTinKHSX ? floatval($thongTinKHSX['soLuongCanSX']) : 0;
                                                $tongSLCan = $dinhMuc * $soLuongCanSX;
                                                $thieuHut = max(0, $tongSLCan - $tonKho);
                                                $cssClass = ($tonKho < $tongSLCan) ? 'table-warning' : '';
                                                if ($thieuHut > 0) $tongThieuHut++;
                                            ?>
                                            <tr class="<?php echo $cssClass; ?>">
                                                <td class="text-center"><?php echo $stt; ?></td>
                                                <td class="text-center fw-bold"><?php echo isset($item['maNL']) ? $item['maNL'] : ''; ?></td>
                                                <td>
                                                    <?php echo isset($item['tenNL']) ? htmlspecialchars($item['tenNL']) : ''; ?>
                                                    <?php if ($thieuHut > 0): ?>
                                                        <span class="badge bg-danger ms-2">Thiếu</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><?php echo isset($item['donViTinh']) ? $item['donViTinh'] : ''; ?></td>
                                                <td class="text-end"><?php echo number_format($dinhMuc, 5, ',', '.'); ?></td>
                                                <td class="text-end <?php echo ($tonKho < $tongSLCan) ? 'text-danger fw-bold' : 'text-success fw-bold'; ?>">
                                                    <?php echo number_format($tonKho, 2, ',', '.'); ?>
                                                </td>
                                                <td class="text-end text-primary fw-bold">
                                                    <?php echo number_format($tongSLCan, 2, ',', '.'); ?>
                                                </td>
                                                <td class="p-1">
                                                    <input type="text" 
                                                           class="form-control form-control-sm text-end" 
                                                           name="soLuongYeuCau[]" 
                                                           placeholder="<?php echo number_format($tongSLCan, 2, ',', '.'); ?>"
                                                           value="<?php echo number_format($tongSLCan, 2, ',', '.'); ?>"
                                                           oninput="formatNumber(this)" 
                                                           required>
                                                    <input type="hidden" name="maNguyenLieu[]" value="<?php echo isset($item['maNL']) ? $item['maNL'] : ''; ?>">
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="8" class="text-end py-2">
                                                    <span class="me-3">
                                                        <i class="fas fa-list me-1"></i>
                                                        Tổng số loại NL: <strong class="text-primary"><?php echo $stt; ?></strong>
                                                    </span>
                                                    <?php if ($thongTinKHSX): ?>
                                                    <span class="me-3">
                                                        <i class="fas fa-industry me-1"></i>
                                                        SL cần SX: <strong class="text-primary"><?php echo number_format($thongTinKHSX['soLuongCanSX'], 0, ',', '.'); ?></strong>
                                                    </span>
                                                    <?php endif; ?>
                                                    <?php if ($tongThieuHut > 0): ?>
                                                    <span class="text-danger">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Thiếu hụt: <strong><?php echo $tongThieuHut; ?></strong> loại
                                                    </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <?php if ($tongThieuHut > 0): ?>
                                <div class="alert alert-warning m-3 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Cảnh báo:</strong> Có <strong><?php echo $tongThieuHut; ?></strong> loại nguyên liệu có tồn kho không đủ (dòng nền vàng). Vui lòng kiểm tra và điều chỉnh số lượng yêu cầu phù hợp.
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i>
                                    <h5 class="mb-2">
                                        <?php if ($maKHSX_Selected > 0): ?>
                                            Kế hoạch này chưa có định mức nguyên liệu
                                        <?php else: ?>
                                            Chưa chọn kế hoạch sản xuất
                                        <?php endif; ?>
                                    </h5>
                                    <p class="text-muted mb-0">
                                        <?php if ($maKHSX_Selected > 0): ?>
                                            Vui lòng liên hệ bộ phận kế hoạch để cập nhật định mức nguyên liệu.
                                        <?php else: ?>
                                            Vui lòng chọn xưởng và kế hoạch sản xuất ở trên để xem định mức nguyên liệu.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- GHI CHÚ -->
                    <div class="card shadow-sm mb-3 border-secondary">
                        <div class="card-header bg-secondary text-white fw-bold">
                            <i class="fas fa-sticky-note me-2"></i> Ghi Chú Bổ Sung
                        </div>
                        <div class="card-body">
                            <textarea class="form-control" 
                                      name="ghiChu" 
                                      rows="3" 
                                      placeholder="Nhập ghi chú về yêu cầu đặc biệt, thời gian cần gấp, hoặc thông tin bổ sung khác..."><?php echo isset($_POST['ghiChu']) ? htmlspecialchars($_POST['ghiChu']) : ''; ?></textarea>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Ghi chú này sẽ giúp bộ phận kho hiểu rõ hơn về yêu cầu của bạn.
                            </small>
                        </div>
                    </div>

                    <!-- NÚT THAO TÁC -->
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-body bg-light">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <button type="button" 
                                            class="btn btn-outline-secondary btn-lg w-100" 
                                            onclick="if(confirm('Bạn có chắc muốn hủy bỏ? Dữ liệu đã nhập sẽ bị mất.')) { window.location.href='dspycnl.php'; }">
                                        <i class="fas fa-times-circle me-2"></i> Hủy Bỏ
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" 
                                            class="btn btn-success btn-lg w-100 shadow-sm" 
                                            name="btnGuiYeuCau"
                                            <?php echo (empty($dinhMucByKHSX) || $maKHSX_Selected <= 0 || $maXuong <= 0) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-paper-plane me-2"></i> Gửi Yêu Cầu Nguyên Liệu
                                    </button>
                                </div>
                            </div>
                            <?php if (empty($dinhMucByKHSX) || $maKHSX_Selected <= 0 || $maXuong <= 0): ?>
                            <div class="alert alert-danger mt-3 mb-0">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <strong>Không thể gửi yêu cầu:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php if ($maXuong <= 0): ?>
                                    <li>Vui lòng chọn xưởng</li>
                                    <?php endif; ?>
                                    <?php if ($maKHSX_Selected <= 0): ?>
                                    <li>Vui lòng chọn kế hoạch sản xuất</li>
                                    <?php endif; ?>
                                    <?php if ($maKHSX_Selected > 0 && empty($dinhMucByKHSX)): ?>
                                    <li>Kế hoạch này chưa có định mức nguyên liệu</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
    </div>
</div>

<script>
function formatNumber(input) {
    var value = input.value.replace(/[^\d,]/g, '');
    var parts = value.split(',');
    var integerPart = parts[0].replace(/\./g, '');
    var decimalPart = parts.length > 1 ? ',' + parts[1] : '';
    
    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    input.value = integerPart + decimalPart;
}

// Tự động submit form khi chọn kế hoạch sản xuất
document.addEventListener('DOMContentLoaded', function() {
    var selectKHSX = document.getElementById('keHoachSX');
    if (selectKHSX) {
        selectKHSX.addEventListener('change', function() {
            if (this.value) {
                this.form.submit();
            }
        });
    }
});
</script>

<?php include_once("../../layout/footer.php"); ?>