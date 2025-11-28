<?php
// BẢO ĐẢM RẰNG CÁC FILE INCLUDE TỒN TẠI VÀ CHÍNH XÁC ĐƯỜNG DẪN
include_once('../../layout/giaodien/pkh.php');
include_once('../../class/clskehoachsx.php');

session_start();

$model = new KeHoachModel();

// === XỬ LÝ ĐẦU VÀO FORM (QUAN TRỌNG) ===

// 1. Lấy đơn hàng được chọn
$maDH_chon = isset($_POST['chonDH']) ? $_POST['chonDH'] : '';

// 2. Lấy sản phẩm được chọn (Dùng để lọc và hiển thị lô)
$maSP_chon = isset($_POST['chonSP']) ? $_POST['chonSP'] : '';

/**
 * Hàm phân lô tự động: Chia đôi số lượng sản phẩm nếu >= 2500, lặp lại cho đến khi mọi lô < 2500.
 * @return array Mảng chứa danh sách các Lô đã chia.
 */
function phanLoTuDong($maSP, $tenSP, $loaiSP, $donViTinh, $soLuongGoc, $moTa, $dinhMucNL) {
    $lots = array();
    // Khởi tạo hàng đợi với lô gốc
    $temp_queue = array(array('soLuong' => $soLuongGoc)); 
    $lotIndex = 1;

    // Lặp để chia nhỏ lô
    while (!empty($temp_queue)) {
        $current = array_shift($temp_queue);
        $soLuong = $current['soLuong'];

        if ($soLuong >= 2500) {
            // Chia đôi lô
            $lo1 = floor($soLuong / 2);
            $lo2 = $soLuong - $lo1;

            // Đưa hai lô mới vào hàng đợi để kiểm tra tiếp
            $temp_queue[] = array('soLuong' => $lo1);
            $temp_queue[] = array('soLuong' => $lo2);
        } else if ($soLuong > 0) {
            // Số lượng đạt yêu cầu (< 2500), thêm vào danh sách lô cuối cùng
            $lots[] = array(
                'maSP' => $maSP,
                'tenSP' => $tenSP,
                'loaiSP' => $loaiSP,
                'donViTinh' => $donViTinh,
                'soLuong' => (int)$soLuong,
                'moTa' => $moTa,
                'maLo' => $maSP . '-' . str_pad($lotIndex++, 3, '0', STR_PAD_LEFT), // Gán Mã Lô duy nhất
                'nguyenLieuDinhMuc' => $dinhMucNL // Định mức NL cho SP này
            );
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

                    // Thực hiện phân lô cho sản phẩm được chọn duy nhất
                    $danhsach_lo_san_xuat = phanLoTuDong(
                        $sp['maSP'], $sp['tenSP'], $sp['loaiSP'], $sp['donViTinh'], 
                        $soLuongGoc, $sp['moTa'], $dinhMucNL
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
            <form method="post"> 
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

                <div class="row">
                    <!--<div class="col-md-4">
                        <label class="form-label fw-semibold">Hình thức sản xuất</label>
                        <select class="form-select" name="hinhThucSX">
                            <option value="Theo đơn hàng">Theo đơn hàng</option>
                            <option value="Theo lô">Theo lô</option>
                        </select>
                    </div>-->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ngày bắt đầu</label>
                        <input type="date" class="form-control" name="ngayBatDau">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ngày kết thúc</label>
                        <input type="date" class="form-control" name="ngayKetThuc">
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
                    $lo_index = 1;
                    foreach ($danhsach_lo_san_xuat as $lo) :
                        $soLuongLo = $lo['soLuong'];
                        $maLo = $lo['maLo'];
                ?>

                <div class="card mb-3 border-primary">
                    <div class="card-header bg-primary-subtle fw-bold text-dark">
                        <i class="bi bi-box-fill me-2"></i> LÔ SẢN XUẤT #<?php echo $lo_index++; ?> | Mã Lô: <span class="text-danger"><?php echo htmlspecialchars($maLo); ?></span>
                        | Sản phẩm: <?php echo htmlspecialchars($lo['tenSP']); ?> (Mã: <?php echo htmlspecialchars($lo['maSP']); ?>)
                        | Số lượng Lô: <span class="fw-bolder"><?php echo number_format($soLuongLo, 0, ',', '.'); ?></span> <?php echo htmlspecialchars($lo['donViTinh']); ?>
                        
                        <input type="hidden" name="loSanPham[<?php echo $maLo; ?>][maSP]" value="<?php echo htmlspecialchars($lo['maSP']); ?>">
                        <input type="hidden" name="loSanPham[<?php echo $maLo; ?>][soLuong]" value="<?php echo htmlspecialchars($soLuongLo); ?>">
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
                                                <select class="form-select form-select-sm" name="phuongAnNL[' . $maLo . '][' . $nl['maNL'] . ']">
                                                    <option value="co_san">Đủ (Có sẵn)</option>
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
                    <textarea class="form-control" name="ghiChu" rows="3" placeholder="Nhập ghi chú..."></textarea>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="reset" class="btn btn-secondary me-2">Làm mới</button>
                    <button type="submit" name="action" value="save_plan" class="btn btn-success">Lưu kế hoạch</button>
                </div>
                
                <?php endif; ?>
                
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php echo isset($list_modal) ? $list_modal : ''; ?>

</div>

<?php include_once("../../layout/footer.php"); ?>

<?php
// XỬ LÝ LƯU KẾ HOẠCH (DÙNG POST)
session_start();
if (isset($_POST['action']) && $_POST['action'] === 'save_plan') {
    $maDH = $_POST['chonDH'];
    $nguoiLap = $_SESSION['hoTen'];
    $ngayLap = date('Y-m-d');
    $ngayBatDau = $_POST['ngayBatDau'];
    $ngayKetThuc = $_POST['ngayKetThuc'];
    $ghiChuGoc = $_POST['ghiChu'];
    // Đảm bảo sử dụng array()
    $loSanPhamData = isset($_POST['loSanPham']) ? $_POST['loSanPham'] : array();
    $phuongAnNLData = isset($_POST['phuongAnNL']) ? $_POST['phuongAnNL'] : array();

    if (empty($loSanPhamData)) {
         echo "<script>alert('❌ Không có lô sản xuất nào được tạo để lưu!');</script>";
         exit;
    }
    
    $count_success = 0;
    // Cần khởi tạo lại Model vì session_start() sau HTML có thể đã bị mất kết nối cũ
    $model = new KeHoachModel(); 

    // Lặp qua từng LÔ SẢN XUẤT đã được gửi lên từ form (chỉ các lô của sản phẩm đã chọn)
    foreach ($loSanPhamData as $maLo => $loInfo) {
        $maSP = $loInfo['maSP'];
        $soLuongLo = (int)$loInfo['soLuong'];
        
        $ghiChuLo = $ghiChuGoc . " (Lô: $maLo - SP: $maSP, SL: $soLuongLo)";

        // 1. TẠO BẢN GHI KẾ HOẠCH MỚI CHO TỪNG LÔ
        // Mã KHSX (maKHSX) sẽ đóng vai trò là Mã Lô/Lệnh sản xuất
        $maKHSX = $model->insertKeHoachSX($maDH, $nguoiLap, $ngayLap, $ngayBatDau, $ngayKetThuc, $ghiChuLo); 

        if ($maKHSX) {
            $count_success++;
            // Lấy định mức nguyên liệu cho sản phẩm này
            $danhsach_nl = $model->getNguyenLieuTheoSanPham($maSP); 

            // 2. LƯU CHI TIẾT NGUYÊN LIỆU CHO LÔ MỚI
            foreach ($danhsach_nl as $nl) {
                $maNL = $nl['maNL'];
                $soLuong1SP = $nl['soLuongTheoSP'];
                $tongSLCan = $soLuong1SP * $soLuongLo;
                $slTonTaiKho = $nl['soLuongTon'];
                $slThieuHut = max(0, $tongSLCan - $slTonTaiKho);
                
                // Lấy Phương án xử lý đã chọn cho Lô và Nguyên liệu này
                $phuongAn = isset($phuongAnNLData[$maLo][$maNL]) ? $phuongAnNLData[$maLo][$maNL] : 'co_san'; 

                $model->insertChiTietNguyenLieuKHSX($maKHSX, $maSP, $maNL, $soLuong1SP, $tongSLCan, $slTonTaiKho, $slThieuHut, $phuongAn);
            }
        }
    }
    
    // Cập nhật trạng thái Đơn hàng chỉ 1 lần sau khi xử lý các lô
    if ($count_success > 0) {
        $model->updateTrangThaiDonHang($maDH, 'Đã lập Kế hoạch Lô');
        echo "<script>alert('✅ Lưu thành công {$count_success} Kế hoạch Lô!'); window.location.href=window.location.pathname;</script>";
        exit;
    } else {
        echo "<script>alert('❌ Lưu kế hoạch thất bại! Vui lòng kiểm tra lại kết nối CSDL và dữ liệu đơn hàng.');</script>";
    }
}
?>