<?php

session_start(); 

include_once('../../layout/giaodien/pkh.php');
include_once('../../class/clskehoachsx.php');

$model = new KeHoachModel();

// === XỬ LÝ LƯU KẾ HOẠCH ===
if (isset($_POST['action']) && $_POST['action'] === 'save_plan') {
    $maDH = $_POST['chonDH'];
    $maSP_lapKH = isset($_POST['chonSP']) ? $_POST['chonSP'] : '';
    $nguoiLap = $_SESSION['hoTen'];
    $ngayLap = date('Y-m-d');
    $ngayBatDau = $_POST['ngayBatDau'];
    $ngayKetThuc = $_POST['ngayKetThuc']; 
    $ghiChu = $_POST['ghiChu'];

    // === VALIDATION NGÀY ===
    $today = date('Y-m-d');
    $startDate = strtotime($ngayBatDau);
    $endDate = strtotime($ngayKetThuc);
    
    // Kiểm tra ngày bắt đầu không nhỏ hơn hôm nay
    if ($ngayBatDau < $today) {
        echo "<script>
            alert('❌ Ngày bắt đầu không được nhỏ hơn ngày hiện tại!');
            window.history.back();
        </script>";
        exit;
    }
    
    // Kiểm tra ngày kết thúc phải lớn hơn ngày bắt đầu ít nhất 3 ngày
    $diffDays = ($endDate - $startDate) / (60 * 60 * 24);
    if ($diffDays < 3) {
        echo "<script>
            alert('❌ Ngày kết thúc phải lớn hơn ngày bắt đầu ít nhất 3 ngày!');
            window.history.back();
        </script>";
        exit;
    }

    $loSanPhamData = isset($_POST['loSanPham']) ? $_POST['loSanPham'] : array();
    $phuongAnNLData = isset($_POST['phuongAnNL']) ? $_POST['phuongAnNL'] : array();

    if (empty($maSP_lapKH) || empty($loSanPhamData)) {
        echo "<script>
            alert('❌ Vui lòng chọn sản phẩm và đảm bảo có lô sản xuất để lập kế hoạch!');
            window.history.back();
        </script>";
    } else {
        // Tạo kế hoạch chung
        $ghiChuKH = $ghiChu . " Kế hoạch chung cho SP: $maSP_lapKH, ĐH: $maDH";
        $maKHSX = $model->insertKeHoachSX($maDH, $nguoiLap, $ngayLap, $ngayBatDau, $ngayKetThuc, $ghiChuKH);

        if ($maKHSX) {
            $count_success = 0;

            foreach ($loSanPhamData as $maLo => $loInfo) {
                $maSP = $maSP_lapKH;
                $soLuongLo = (int)$loInfo['soLuong'];

                // Dùng mã lô đã sinh từ phanLoTuDong
                $insertLotOK = $model->insertLoSanPham($maLo, $maKHSX, $maSP, $ngayBatDau, $soLuongLo);
                if (!$insertLotOK) {
                    error_log("Lỗi insert lô: $maLo");
                    continue;
                }

                $danhsach_nl = $model->getNguyenLieuTheoSanPham($maSP);
                $success_lot = true;

                foreach ($danhsach_nl as $nl) {
                    $maNL = $nl['maNL'];
                    $soLuong1SP = $nl['soLuongTheoSP'];
                    $tongSLCan = $soLuong1SP * $soLuongLo;
                    $slTonTaiKho = (float)$nl['soLuongTon'];
                    $slThieuHut = max(0, $tongSLCan - $slTonTaiKho);

                    $phuongAn = isset($phuongAnNLData[$maLo][$maNL]) ? $phuongAnNLData[$maLo][$maNL] : 'co_san';

                    $result = $model->insertChiTietNguyenLieuKHSX(
                        $maKHSX, $maSP, $maNL, $soLuong1SP, $tongSLCan, $slTonTaiKho, $slThieuHut, $phuongAn, $maLo
                    );

                    if (!$result) {
                        $success_lot = false;
                        error_log("Lỗi insert chi tiết NL: KHSX=$maKHSX, Mã NL=$maNL, Lô=$maLo");
                        break;
                    }
                }

                if ($success_lot) $count_success++;
            }

            if ($count_success > 0) {
                $model->updateTrangThaiDonHang($maDH, 'Chờ xử lý');
                echo "<script>
                    alert('✅ Lưu thành công {$count_success} lô sản xuất thuộc KHSX mã {$maKHSX}!');
                    window.location.href = window.location.pathname + '?chonDH=' + '{$maDH}';
                </script>";
            } else {
                echo "<script>
                    alert('❌ Lưu chi tiết nguyên liệu thất bại! Đã tạo KHSX mã {$maKHSX} nhưng không có lô nào được lưu.');
                    window.history.back();
                </script>";
            }
        } else {
            echo "<script>
                alert('❌ Lưu kế hoạch thất bại! Lỗi kết nối CSDL hoặc dữ liệu.');
                window.history.back();
            </script>";
        }
    }
}

// === XỬ LÝ ĐẦU VÀO FORM (SAU KHI XỬ LÝ POST) ===

// 1. Lấy đơn hàng được chọn (ưu tiên từ POST, sau đó từ GET)
$maDH_chon = isset($_POST['chonDH']) ? $_POST['chonDH'] : (isset($_GET['chonDH']) ? $_GET['chonDH'] : '');

// 2. Lấy sản phẩm được chọn (Dùng để lọc và hiển thị lô)
$maSP_chon = isset($_POST['chonSP']) ? $_POST['chonSP'] : '';

/**
 * Hàm phân lô tự động: Chia đôi số lượng sản phẩm nếu > 2500,
 * lặp lại cho đến khi mọi lô < 2500.
 * @return array Danh sách các lô sau khi chia.
 */
function phanLoTuDong($maSP, $tenSP, $loaiSP, $donViTinh, $soLuongGoc, $moTa, $dinhMucNL, $maKHSX = '000') {
    $lots = array();
    $temp_queue = array(array('soLuong' => $soLuongGoc)); 
    $lotIndex = 1;

    // Lặp chia nhỏ lô
    while (!empty($temp_queue)) {
        $current = array_shift($temp_queue);
        $soLuong = $current['soLuong'];

        if ($soLuong > 2500) {
            // Chia đôi lô
            $lo1 = floor($soLuong / 2);
            $lo2 = $soLuong - $lo1;

            // Cho 2 lô vào hàng đợi
            $temp_queue[] = array('soLuong' => $lo1);
            $temp_queue[] = array('soLuong' => $lo2);

        } else if ($soLuong > 0) {
            // Tạo mã lô
            $maLo = date('ymdhis') . rand(100, 999);

            // Lưu lô
            $lots[] = array(
                'maSP' => $maSP,
                'tenSP' => $tenSP,
                'loaiSP' => $loaiSP,
                'donViTinh' => $donViTinh,
                'soLuong' => (int)$soLuong,
                'moTa' => $moTa,
                'maLo' => $maLo,
                'nguyenLieuDinhMuc' => $dinhMucNL
            );

            $lotIndex++;
        }
    }

    return $lots;
}

// Lấy danh sách đơn hàng chờ
$danhsach_dh = $model->getDSDonHangCho();

// Lấy danh sách sản phẩm theo đơn hàng đã chọn
$danhsach_sp = array();
$danhsach_lo_san_xuat = array();
$sp_chon = null; // Biến lưu thông tin sản phẩm được chọn

if ($maDH_chon != '') {
    $danhsach_sp = $model->getSanPhamTheoDonHang($maDH_chon);
    
    // ----------------------------------------------------
    // CHỈ PHÂN LÔ CHO SẢN PHẨM ĐÃ ĐƯỢC CHỌN (maSP_chon)
    // ----------------------------------------------------
    if ($maSP_chon != '') {
        foreach ($danhsach_sp as $sp) {
            if ($sp['maSP'] == $maSP_chon) {
                $sp_chon = $sp;
                $soLuongGoc = (int)$sp['soLuong'];
                
                if ($soLuongGoc > 0) {
                    $dinhMucNL = $model->getNguyenLieuTheoSanPham($sp['maSP']); 

                    // Sửa: Thêm tham số maKHSX mặc định
                    $danhsach_lo_san_xuat = phanLoTuDong(
                        $sp['maSP'], $sp['tenSP'], $sp['loaiSP'], $sp['donViTinh'], 
                        $soLuongGoc, $sp['moTa'], $dinhMucNL, '000'
                    );
                }
                break;
            }
        }
    }
}
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
            <form method="post">
                <table class="table table-bordered table-hover align-middle text-center m-0">
                    <thead class="thead-blue">
                        <tr>
                            <th style="width:5%">Chọn</th>
                            <th>Mã ĐH</th>
                            <th>Ngày đặt</th>
                            <th>Ngày giao dự kiến</th>
                            <th >Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (is_array($danhsach_dh) && count($danhsach_dh) > 0) {
                            $maDH_da_xu_ly = array();
                            $list_modal = ''; 

                            foreach ($danhsach_dh as $row) {
                                $checked = ($maDH_chon == $row['maDH']) ? 'checked' : '';

                                echo "<tr>";
                                echo "<td>
                                        <input type='radio' name='chonDH' value='{$row['maDH']}' $checked onchange='this.form.submit()'>
                                    </td>";
                                echo "<td>" . htmlspecialchars($row['maDH']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['ngayDat']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['ngayGiaoDuKien']) . "</td>";
                                echo "<td><span class='badge bg-info text-dark'>" . htmlspecialchars($row['trangThai']) . "</span></td>";
                                echo "<td>
                                        <button type='button' class='btn btn-sm btn-outline-primary' 
                                            data-bs-toggle='modal' data-bs-target='#modal_{$row['maDH']}'>
                                            <i class='bi bi-eye'></i> Xem chi tiết
                                        </button>
                                    </td>";
                                echo "</tr>";

                                // ========== Gom modal ra ngoài cuối (Chỉ tạo modal duy nhất cho mỗi mã ĐH) ==========
                                if (!in_array($row['maDH'], $maDH_da_xu_ly)) {
                                    $maDH_da_xu_ly[] = $row['maDH'];

                                    ob_start();
                                    ?>
                                    <div class="modal fade" id="modal_<?php echo $row['maDH']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h6 class="modal-title fw-bold">
                                                        <i class="bi bi-file-earmark-text me-2"></i>Chi tiết Đơn hàng <?php echo htmlspecialchars($row['maDH']); ?>
                                                    </h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="card mb-3">
                                                        <div class="card-header bg-secondary text-white fw-semibold">
                                                            <i class="bi bi-info-circle me-2"></i>Thông tin chung
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row mb-2">
                                                                <div class="col-md-3"><strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($row['maDH']); ?></div>
                                                                <div class="col-md-3"><strong>Ngày đặt:</strong> <?php echo htmlspecialchars($row['ngayDat']); ?></div>
                                                                <div class="col-md-3"><strong>Ngày giao dự kiến:</strong> <?php echo htmlspecialchars($row['ngayGiaoDuKien']); ?></div>
                                                                <div class="col-md-3"><strong>Trạng thái:</strong> <?php echo htmlspecialchars($row['trangThai']); ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card mb-3">
                                                        <div class="card-header bg-secondary text-white fw-semibold">
                                                            <i class="bi bi-person-vcard me-2"></i>Thông tin khách hàng
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row mb-2">
                                                                <div class="col-md-4"><strong>Tên khách hàng:</strong> <?php echo htmlspecialchars($row['tenKH']); ?></div>
                                                                <div class="col-md-4"><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></div>
                                                                <div class="col-md-4"><strong>Điện thoại:</strong> <?php echo htmlspecialchars($row['sDT']); ?></div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($row['diaChi']); ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card">
                                                        <div class="card-header bg-info fw-semibold text-dark">
                                                            <i class="bi bi-box-seam me-2"></i>Danh sách sản phẩm
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <table class="table table-bordered table-striped mb-0 text-center">
                                                                <thead class="bg-primary text-white">
                                                                    <tr>
                                                                        <th>#</th><th>Mã SP</th><th>Tên sản phẩm</th><th>Loại</th><th>Đơn vị</th><th>Số lượng</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $dssp_modal = $model->getSanPhamTheoDonHang($row['maDH']); 
                                                                    $i = 1;
                                                                    foreach ($dssp_modal as $sp) {
                                                                        echo "<tr>
                                                                                <td>{$i}</td>
                                                                                <td>" . htmlspecialchars($sp['maSP']) . "</td>
                                                                                <td>" . htmlspecialchars($sp['tenSP']) . "</td>
                                                                                <td>" . htmlspecialchars($sp['loaiSP']) . "</td>
                                                                                <td>" . htmlspecialchars($sp['donViTinh']) . "</td>
                                                                                <td>" . htmlspecialchars($sp['soLuong']) . "</td>
                                                                              </tr>";
                                                                        $i++;
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    $list_modal .= ob_get_clean();
                                }
                            }
                        } else {
                            echo "<tr><td colspan='9' class='text-muted'>Không có đơn hàng nào ở trạng thái chờ lập kế hoạch.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <?php if ($maDH_chon != ''): ?>
    <div id="plan-form-container" class="card shadow-sm">
        <div class="card-header bg-primary text-white fw-bold">Thiết lập kế hoạch sản xuất</div>
        <div class="card-body">
            <form method="post" id="planForm"> 
                <input type="hidden" name="chonDH" value="<?php echo htmlspecialchars($maDH_chon); ?>"> 
                <input type="hidden" name="chonSP" value="<?php echo htmlspecialchars($maSP_chon); ?>"> 

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mã Đơn hàng</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($maDH_chon); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Người lập</label>
                        <input type="text" class="form-control" name="nguoiLap"
                            value="<?php echo htmlspecialchars($_SESSION['hoTen'])?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ngày lập</label>
                        <input type="text" class="form-control" name="ngayLap" value="<?php echo date('d/m/Y'); ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="ngayBatDau" id="ngayBatDau" required
                               min="<?php echo date('Y-m-d'); ?>">
                        <div class="form-text text-muted small">Ngày bắt đầu phải từ hôm nay trở đi</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ngày kết thúc <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="ngayKetThuc" id="ngayKetThuc" required>
                        <div class="form-text text-muted small">Ngày kết thúc phải sau ngày bắt đầu ít nhất 3 ngày</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Số ngày sản xuất</label>
                        <input type="text" class="form-control" id="soNgaySX" value="0" readonly>
                        <div class="form-text text-muted small">Tự động tính khi chọn ngày</div>
                    </div>
                </div>

                <hr>
                
                <h6 class="fw-bold text-primary">Danh sách sản phẩm trong đơn hàng (Chọn sản phẩm để phân lô)</h6>
                <table class="table table-bordered table-hover align-middle text-center m-0">
                    <thead class="thead-blue">
                        <tr>
                            <th style="width:5%">Chọn</th>
                            <th>Mã SP</th>
                            <th>Tên sản phẩm</th>
                            <th>Loại</th>
                            <th>Đơn vị</th>
                            <th>Số lượng ĐH</th>
                            <th>Mô tả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (is_array($danhsach_sp) && count($danhsach_sp) > 0) {
                            foreach ($danhsach_sp as $sp) {
                                $checked = ($maSP_chon == $sp['maSP']) ? 'checked' : '';
                                echo "<tr>";
                                echo "<td>
                                        <input type='radio' name='chonSP' value='{$sp['maSP']}' $checked onchange='this.form.submit()'>
                                    </td>";
                                echo "<td>" . htmlspecialchars($sp['maSP']) . "</td>";
                                echo "<td>" . htmlspecialchars($sp['tenSP']) . "</td>";
                                echo "<td>" . htmlspecialchars($sp['loaiSP']) . "</td>";
                                echo "<td>" . htmlspecialchars($sp['donViTinh']) . "</td>";
                                echo "<td>" . number_format($sp['soLuong'], 0, ',', '.') . "</td>";
                                echo "<td>" . htmlspecialchars($sp['moTa']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-muted'>Không có sản phẩm nào trong đơn hàng này.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <hr>
                
                <?php if ($maSP_chon != '' && $sp_chon): ?>
                
                <h6 class="fw-bold text-success">Chi tiết phân lô cho sản phẩm: <span class="text-primary"><?php echo htmlspecialchars($sp_chon['tenSP']); ?></span> (SL gốc: <?php echo number_format($sp_chon['soLuong'], 0, ',', '.'); ?>)</h6>
                <p class="text-muted"><small>Hệ thống đã tự động chia thành **<?php echo count($danhsach_lo_san_xuat); ?>** lô. Số lượng tối đa mỗi lô là 2.500.</small></p>

                <?php
                if (is_array($danhsach_lo_san_xuat) && count($danhsach_lo_san_xuat) > 0) {
                    foreach ($danhsach_lo_san_xuat as $index => $lo) :
                        $soLuongLo = $lo['soLuong'];
                        $maLo = $lo['maLo'];
                ?>

                <div class="card mb-3 border-primary">
                    <div class="card-header bg-primary-subtle fw-bold text-dark">
                        <i class="bi bi-box-fill me-2"></i> LÔ SẢN XUẤT #<?php echo ($index + 1); ?> | Mã Lô: <span class="text-danger"><?php echo htmlspecialchars($maLo); ?></span>
                        | Sản phẩm: <?php echo htmlspecialchars($lo['tenSP']); ?> (Mã: <?php echo htmlspecialchars($lo['maSP']); ?>)
                        | Số lượng Lô: <span class="fw-bolder"><?php echo number_format($soLuongLo, 0, ',', '.'); ?></span> <?php echo htmlspecialchars($lo['donViTinh']); ?>
                        
                        <!-- ✅ SỬA: Dùng $maLo làm key -->
                        <input type="hidden" name="loSanPham[<?php echo htmlspecialchars($maLo); ?>][maSP]" value="<?php echo htmlspecialchars($lo['maSP']); ?>">
                        <input type="hidden" name="loSanPham[<?php echo htmlspecialchars($maLo); ?>][soLuong]" value="<?php echo htmlspecialchars($soLuongLo); ?>">
                    </div>
                    
                    <div class="card-body p-0">
                        <h6 class="fw-bold text-success p-3 m-0">Nguyên liệu cần cho Lô Sản Xuất <span class="text-danger"><?php echo htmlspecialchars($maLo); ?></span></h6>
                        <table class="table table-bordered table-sm m-0">
                            <thead class="table-info text-center">
                                <tr>
                                    <th>Mã NL</th>
                                    <th>Tên NL</th>
                                    <th>ĐVT</th>
                                    <th>SL/1sp</th>
                                    <th>Tổng SL cần cho Lô</th>
                                    <th>Số lượng tồn</th>
                                    <th>Thiếu hụt</th>
                                    <th>Phương án xử lý</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <?php
                                $danhsach_nl_cho_lo = $lo['nguyenLieuDinhMuc'];
                                if(is_array($danhsach_nl_cho_lo) && count($danhsach_nl_cho_lo) > 0){
                                    foreach($danhsach_nl_cho_lo as $nl){
                                        $tongSLNL = $nl['soLuongTheoSP'] * $soLuongLo;
                                        $soLuongTon = (float)($nl['soLuongTon']);
                                        $thieuHut = max(0, $tongSLNL - $soLuongTon);
    
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($nl['maNL']) . '</td>';
                                        echo '<td>' . htmlspecialchars($nl['tenNL']) . '</td>';
                                        echo '<td>' . htmlspecialchars($nl['donViTinh']) . '</td>';
                                        echo '<td>' . htmlspecialchars($nl['soLuongTheoSP']) . '</td>';
                                        echo '<td class="bg-warning-subtle">' . $tongSLNL . '</td>';
                                        echo '<td>' . $soLuongTon . '</td>';
                                        echo '<td class="' . ($thieuHut > 0 ? 'text-danger fw-bold' : '') . '">' . $thieuHut . '</td>';
                                        echo '<td>
                                                <select class="form-select form-select-sm" name="phuongAnNL[' . htmlspecialchars($maLo) . '][' . htmlspecialchars($nl['maNL']) . ']">
                                                    <option value="co_san"' . ($thieuHut <= 0 ? ' selected' : '') . '>Đủ (Có sẵn)</option>
                                                    <option value="mua_moi"' . ($thieuHut > 0 ? ' selected' : '') . '>Mua bổ sung</option>
                                                    <option value="dieu_chuyen">Điều chuyển kho</option>
                                                </select>
                                              </td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="8" class="text-danger">Sản phẩm này chưa có định mức nguyên liệu.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php endforeach; 
                } else {
                    echo '<div class="alert alert-warning">Sản phẩm này không đủ điều kiện để chia lô hoặc số lượng bằng 0.</div>';
                }
                ?>
                <hr>

                <div class="mt-3">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <textarea class="form-control" name="ghiChu" rows="3" placeholder="Nhập ghi chú về kế hoạch sản xuất..."></textarea>
                </div>

        
                <div class="d-flex justify-content-end mt-3">
                    <button type="reset" class="btn btn-secondary me-2">Làm mới</button>
                    <button type="submit" name="action" value="save_plan" class="btn btn-success">Lưu kế hoạch</button>
                </div>
                
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Vui lòng chọn một sản phẩm để xem chi tiết phân lô sản xuất.
                    </div>
                <?php endif; ?>
                
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php echo isset($list_modal) ? $list_modal : ''; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ngayBatDauInput = document.getElementById('ngayBatDau');
    const ngayKetThucInput = document.getElementById('ngayKetThuc');
    const soNgaySXInput = document.getElementById('soNgaySX');
    const planForm = document.getElementById('planForm');
    
    // Hàm tính số ngày giữa hai ngày
    function tinhSoNgay(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    }
    
    // Hàm định dạng ngày
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('vi-VN');
    }
    
    // Xử lý khi ngày bắt đầu thay đổi
    if (ngayBatDauInput) {
        // Set min cho ngày bắt đầu là hôm nay
        ngayBatDauInput.min = new Date().toISOString().split('T')[0];
        
        ngayBatDauInput.addEventListener('change', function() {
            if (this.value) {
                // Tính ngày tối thiểu cho ngày kết thúc (+3 ngày)
                const minEndDate = new Date(this.value);
                minEndDate.setDate(minEndDate.getDate() + 3);
                
                const minEndDateStr = minEndDate.toISOString().split('T')[0];
                ngayKetThucInput.min = minEndDateStr;
                ngayKetThucInput.value = ''; // Reset ngày kết thúc
                
                // Hiển thị thông báo
                const formattedDate = formatDate(minEndDateStr);
                ngayKetThucInput.setAttribute('placeholder', `Từ ${formattedDate} trở đi`);
                soNgaySXInput.value = '0';
                
                // Focus vào ngày kết thúc
                ngayKetThucInput.focus();
            }
        });
    }
    
    // Xử lý khi ngày kết thúc thay đổi
    if (ngayKetThucInput) {
        ngayKetThucInput.addEventListener('change', function() {
            if (ngayBatDauInput.value && this.value) {
                const startDate = new Date(ngayBatDauInput.value);
                const endDate = new Date(this.value);
                const diffDays = tinhSoNgay(ngayBatDauInput.value, this.value);
                
                if (diffDays < 3) {
                    alert('❌ Ngày kết thúc phải lớn hơn ngày bắt đầu ít nhất 3 ngày!');
                    
                    // Tính ngày tối thiểu
                    const minEndDate = new Date(startDate);
                    minEndDate.setDate(minEndDate.getDate() + 3);
                    this.value = minEndDate.toISOString().split('T')[0];
                    
                    // Tính lại số ngày
                    soNgaySXInput.value = tinhSoNgay(ngayBatDauInput.value, this.value);
                } else {
                    soNgaySXInput.value = diffDays;
                }
            }
        });
    }
    
    // Xử lý khi cả hai ngày đều có giá trị (tính toán ban đầu)
    if (ngayBatDauInput.value && ngayKetThucInput.value) {
        const diffDays = tinhSoNgay(ngayBatDauInput.value, ngayKetThucInput.value);
        soNgaySXInput.value = diffDays;
    }
    
    // Validation khi submit form
    if (planForm) {
        planForm.addEventListener('submit', function(e) {
            // Kiểm tra các trường bắt buộc
            if (!ngayBatDauInput.value || !ngayKetThucInput.value) {
                alert('❌ Vui lòng nhập đầy đủ ngày bắt đầu và ngày kết thúc!');
                e.preventDefault();
                return false;
            }
            
            // Kiểm tra ngày bắt đầu không nhỏ hơn hôm nay
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const startDate = new Date(ngayBatDauInput.value);
            
            if (startDate < today) {
                alert('❌ Ngày bắt đầu không được nhỏ hơn ngày hiện tại!');
                e.preventDefault();
                ngayBatDauInput.focus();
                return false;
            }
            
            // Kiểm tra ngày kết thúc phải lớn hơn ngày bắt đầu ít nhất 3 ngày
            const endDate = new Date(ngayKetThucInput.value);
            const diffDays = tinhSoNgay(ngayBatDauInput.value, ngayKetThucInput.value);
            
            if (diffDays < 3) {
                alert('❌ Ngày kết thúc phải lớn hơn ngày bắt đầu ít nhất 3 ngày!');
                e.preventDefault();
                ngayKetThucInput.focus();
                return false;
            }
            
            // Kiểm tra đã chọn sản phẩm chưa
            const selectedProduct = document.querySelector('input[name="chonSP"]:checked');
            if (!selectedProduct) {
                alert('❌ Vui lòng chọn sản phẩm để lập kế hoạch!');
                e.preventDefault();
                return false;
            }
            
            // Hiển thị xác nhận
            const confirmed = confirm('Bạn có chắc chắn muốn lưu kế hoạch sản xuất này?');
            if (!confirmed) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    }
    
    // Xử lý reset form
    const resetBtn = planForm ? planForm.querySelector('button[type="reset"]') : null;
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            setTimeout(() => {
                soNgaySXInput.value = '0';
                if (ngayKetThucInput) {
                    ngayKetThucInput.removeAttribute('placeholder');
                    ngayKetThucInput.removeAttribute('min');
                }
            }, 100);
        });
    }
});
</script>

<?php include_once("../../layout/footer.php"); ?>