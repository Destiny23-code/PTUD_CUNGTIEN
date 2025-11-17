<?php
// BẢO ĐẢM RẰNG CÁC FILE INCLUDE TỒN TẠI VÀ CHÍNH XÁC ĐƯỜNG DẪN
include_once('../../layout/giaodien/pkh.php');
include_once('../../class/clskehoachsx.php');

session_start();

$model = new KeHoachModel();

// === XỬ LÝ ĐẦU VÀO FORM (QUAN TRỌNG) ===

// 1. Lấy đơn hàng được chọn
$maDH_chon = isset($_POST['chonDH']) ? $_POST['chonDH'] : '';

// 2. Lấy sản phẩm được chọn (ĐÃ SỬA LỖI: Biến này phải được lấy từ POST)
$maSP_chon = isset($_POST['chonSP']) ? $_POST['chonSP'] : '';

// Lấy danh sách đơn hàng chờ
$danhsach_dh = $model->getDSDonHangCho();

// Lấy danh sách sản phẩm theo đơn hàng đã chọn
$danhsach_sp = array();
$soLuongSP_chon = 0; // Khởi tạo số lượng SP để dùng tính định mức
if ($maDH_chon != '') {
    $danhsach_sp = $model->getSanPhamTheoDonHang($maDH_chon);
    
    // Tìm số lượng của sản phẩm đang được chọn
    foreach ($danhsach_sp as $sp) {
        if ($sp['maSP'] == $maSP_chon) {
            $soLuongSP_chon = (int)$sp['soLuong'];
            break;
        }
    }
}

// Lấy danh sách nguyên liệu theo sản phẩm đã chọn (ĐÃ SỬA LỖI LOGIC: Phải dựa vào $maSP_chon)
$danhsach_nl = array();
if ($maSP_chon != '' && $soLuongSP_chon > 0) {
    // Giả sử hàm này trả về cả định mức, số lượng tồn (từ kho), v.v.
    $danhsach_nl = $model->getNguyenLieuTheoSanPham($maSP_chon); 
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
                            $list_modal = ''; // gom toàn bộ modal ra cuối trang

                            foreach ($danhsach_dh as $row) {
                                // Logic này là để tránh lặp lại thông tin chung của ĐH nếu getDSDonHangCho() trả về các dòng trùng nhau cho các SP khác nhau.
                                // Tuy nhiên, cấu trúc bảng hiện tại hiển thị từng dòng SP riêng lẻ cho mỗi ĐH.
                                // Nếu $danhsach_dh đã là một mảng ĐH duy nhất với các SP lồng trong đó, logic này không cần thiết. 
                                // DỰA TRÊN CẤU TRÚC CODE HIỆN TẠI: Để tránh lặp lại cùng một mã ĐH nhiều lần trên một hàng, tôi sẽ bỏ qua check này.
                                // if (in_array($row['maDH'], $maDH_da_xu_ly)) continue;
                                // $maDH_da_xu_ly[] = $row['maDH'];

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
                                                                    // TỐT HƠN NÊN TÁCH HÀM getSanPhamTheoDonHang VÀO MỘT BIẾN DUY NHẤT Ở ĐẦU TRANG VÀ LỌC LẠI Ở ĐÂY
                                                                    $dssp_modal = $model->getSanPhamTheoDonHang($row['maDH']); // Gọi lại CSDL trong modal (Chấp nhận nếu dữ liệu ít)
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
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Hình thức sản xuất</label>
                        <select class="form-select" name="hinhThucSX">
                            <option value="Theo đơn hàng">Theo đơn hàng</option>
                            <option value="Theo lô">Theo lô</option>
                        </select>
                    </div>
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
                <h6 class="fw-bold text-primary">Danh sách sản phẩm trong đơn hàng (Chọn sản phẩm để xem nguyên liệu)</h6>
                <table class="table table-bordered table-sm mt-2">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Chọn</th>
                            <th>Mã SP</th>
                            <th>Tên sản phẩm</th>
                            <th>Loại</th>
                            <th>ĐVT</th>
                            <th>Số lượng</th>
                            <th>Mô tả</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php
                        if (is_array($danhsach_sp) && count($danhsach_sp) > 0) {
                            foreach ($danhsach_sp as $sp) {
                                // ĐÃ SỬA LỖI: Dùng biến $sp và tạo biến $sp_checked riêng
                                $sp_checked = ($maSP_chon == $sp['maSP']) ? 'checked' : '';
                                
                                echo "<tr>
                                        <td>
                                            <input type='radio' name='chonSP' value='{$sp['maSP']}' $sp_checked onchange='this.form.submit()'>
                                        </td>
                                        <td>" . htmlspecialchars($sp['maSP']) . "</td>
                                        <td>" . htmlspecialchars($sp['tenSP']) . "</td>
                                        <td>" . htmlspecialchars($sp['loaiSP']) . "</td>
                                        <td>" . htmlspecialchars($sp['donViTinh']) . "</td>
                                        <td>" . htmlspecialchars($sp['soLuong']) . "</td>
                                        <td>" . htmlspecialchars($sp['moTa']) . "</td>
                                    </tr>";
                            }
                        } else {
                            // ĐÃ SỬA LỖI: Colspan phải là 7
                            echo "<tr><td colspan='7' class='text-danger'>Không có sản phẩm trong đơn hàng này.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                
                <hr>
                <?php if ($maSP_chon != ''): ?>
                <h6 class="fw-bold text-primary">Nguyên liệu cần cho sản xuất (Tổng hợp cho sản phẩm: <?php echo htmlspecialchars($maSP_chon); ?> - Số lượng: <?php echo htmlspecialchars($soLuongSP_chon); ?>)</h6>
                <table id="dsnl" class="table table-bordered table-sm mt-2">
                    <thead class="table-info text-center">
                        <tr>
                            <th>Mã NL</th>
                            <th>Tên NL</th>
                            <th>ĐVT</th>
                            <th>Định mức</th> 
                            <th>Số lượng/1sp</th>
                            <th>Tổng số lượng theo đơn hàng</th>
                            <th>Số lượng tồn</th>
                            <th>Thiếu hụt</th>
                            <th>Phương án xử lý</th> </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php
                        if(is_array($danhsach_nl) && count($danhsach_nl) > 0){
                            foreach($danhsach_nl as $nl){
                                $tongSLNL = $nl['soLuongTheoSP'] * $soLuongSP_chon;
                                $soLuongTon = (float)($nl['soLuongTon']);
                                $thieuHut = max(0, $tongSLNL - $nl['soLuongTon']); // Tính toán thiếu hụt

                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($nl['maNL']) . '</td>';
                                echo '<td>' . htmlspecialchars($nl['tenNL']) . '</td>';
                                echo '<td>' . htmlspecialchars($nl['donViTinh']) . '</td>';
                                echo '<td>' . htmlspecialchars($nl['dinhMuc']) . '</td>';
                                echo '<td>' . htmlspecialchars($nl['soLuongTheoSP']) . '</td>';
                                echo '<td class="bg-warning-subtle">' . htmlspecialchars($tongSLNL) . '</td>';
                                echo '<td>' . htmlspecialchars($nl['soLuongTon']) . '</td>';
                                echo '<td class="' . ($thieuHut > 0 ? 'text-danger fw-bold' : '') . '">' . htmlspecialchars($thieuHut) . '</td>';
                                echo '<td>
                                        <select class="form-select form-select-sm" name="phuongAnNL[' . $nl['maNL'] . ']">
                                            <option value="co_san">Đủ (Có sẵn)</option>
                                            <option value="mua_moi">Mua bổ sung</option>
                                            <option value="dieu_chuyen">Điều chuyển kho</option>
                                        </select>
                                    </td>';
                                echo '</tr>';
                            }
                        } else {
                            // ĐÃ SỬA LỖI: Colspan phải là 8
                            echo '<tr><td colspan="8" class="text-danger">Sản phẩm này chưa có định mức nguyên liệu hoặc không thể truy xuất dữ liệu.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <?php endif; ?>

                <div class="mt-3">
                    <label class="form-label fw-semibold">Ghi chú</label>
                    <textarea class="form-control" name="ghiChu" rows="3" placeholder="Nhập ghi chú..."></textarea>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="reset" class="btn btn-secondary me-2">Làm mới</button>
                    <button type="submit" name="action" value="save_plan" class="btn btn-success">Lưu kế hoạch</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php echo isset($list_modal) ? $list_modal : ''; ?>

</div>

<?php include_once("../../layout/footer.php"); ?>

<?php
session_start();
if (isset($_POST['action']) && $_POST['action'] === 'save_plan') {
    $maDH = $_POST['chonDH'];
    $nguoiLap = $_SESSION['hoTen'];
    $ngayLap = date('Y-m-d');
    $hinhThucSX = $_POST['hinhThucSX'];
    $ngayBatDau = $_POST['ngayBatDau'];
    $ngayKetThuc = $_POST['ngayKetThuc'];
    $ghiChu = $_POST['ghiChu'];

    // Tạo kế hoạch
    $maKHSX = $model->insertKeHoachSX($maDH, $nguoiLap, $ngayLap, $hinhThucSX, $ngayBatDau, $ngayKetThuc, $ghiChu);

    if ($maKHSX) {
        foreach ($danhsach_sp as $sp) {
            $maSP = $sp['maSP'];
            $soLuongSP = $sp['soLuong'];
            $danhsach_nl = $model->getNguyenLieuTheoSanPham($maSP);

            foreach ($danhsach_nl as $nl) {
                $maNL = $nl['maNL'];
                $soLuong1SP = $nl['soLuongTheoSP'];
                $tongSLCan = $soLuong1SP * $soLuongSP;
                $slTonTaiKho = $nl['soLuongTon'];
                $slThieuHut = max(0, $tongSLCan - $slTonTaiKho);
                $phuongAn = $_POST['phuongAnNL'][$maNL];

                $model->insertChiTietNguyenLieuKHSX($maKHSX, $maSP, $maNL, $soLuong1SP, $tongSLCan, $slTonTaiKho, $slThieuHut, $phuongAn);
            }
        }

        $model->updateTrangThaiDonHang($maDH, 'Chờ xử lý');
        echo "<script>alert('✅ Lưu kế hoạch sản xuất thành công!'); window.location.href=window.location.pathname;</script>";
        exit;
    } else {
        echo "<script>alert('❌ Lưu kế hoạch thất bại!');</script>";
    }
}
?>