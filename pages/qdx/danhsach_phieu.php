<?php
// 1. INCLUDE CÁC FILE CẦN THIẾT
include_once('../../layout/giaodien/qdx.php'); 
include_once('../../class/clsLapPYCKD.php');

// KHỞI TẠO
$lapPYCKD = new clsLapPYCKD();

// 2. LẤY THÔNG TIN NGƯỜI LẬP TỪ SESSION
$nguoiLapHoTen = 'Không xác định';

if (isset($_SESSION['id'])) {
    $idUser = $_SESSION['id'];
    // Gọi hàm lấy tên nhân viên từ Database
    $tenNV = $lapPYCKD->getTenNhanVien($idUser);
    
    if (!empty($tenNV)) {
        $nguoiLapHoTen = $tenNV; 
    } else {
        $nguoiLapHoTen = isset($_SESSION['user']) ? $_SESSION['user'] : 'User '.$idUser;
    }
} else {
    $nguoiLapHoTen = 'Admin Test'; 
}

// 3. LẤY DỮ LIỆU & PHÂN TRANG
$allPhieu = $lapPYCKD->getAllPhieuYCKD();
$totalRecords = count($allPhieu);
$limit = 10; 
$totalPages = ceil($totalRecords / $limit); 
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
if ($currentPage > $totalPages && $totalPages > 0) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $limit;
$danhSachPhieu = array_slice($allPhieu, $offset, $limit);
$danhSachLoChoKiemDinh = $lapPYCKD->getLoSanPhamByTrangThai('Chờ kiểm định');
?>

<style>
    /* CSS Modal */
    .modal { z-index: 10000 !important; }
    .modal-backdrop { z-index: 9999 !important; }
    #lapPhieuModal .modal-header { display: flex; justify-content: space-between; align-items: center; background-color: #0d6efd; color: white; padding: 1rem; border-top-left-radius: 0.3rem; border-top-right-radius: 0.3rem; }
    #lapPhieuModal .close { background: none; border: none; color: white; font-size: 1.5rem; font-weight: bold; opacity: 1; cursor: pointer; padding: 0; margin: 0; line-height: 1; }
    #lapPhieuModal .close:hover { color: #ffcccc; }
    #lapPhieuModal .modal-title { margin: 0; font-weight: 600; }
    #lapPhieuModal .form-control, #lapPhieuModal .form-select { width: 100% !important; display: block; box-sizing: border-box; margin-bottom: 10px; }
    #lapPhieuModal label { font-weight: bold; margin-bottom: 5px; display: block; }
    
    /* CSS Badge */
    .badge-success { background-color: #198754; color: white; }
    .badge-danger { background-color: #dc3545; color: white; }
    .badge-warning { background-color: #ffc107; color: black; }
    .badge-info { background-color: #0dcaf0; color: black; }

    /* CSS Phân trang */
    .pagination { margin-top: 20px; justify-content: flex-end; display: flex; list-style: none; padding: 0; }
    .page-item { margin: 0 2px; }
    .page-link { position: relative; display: block; padding: 0.5rem 0.75rem; margin-left: -1px; line-height: 1.25; color: #0d6efd; background-color: #fff; border: 1px solid #dee2e6; text-decoration: none; border-radius: 0.25rem; }
    .page-link:hover { background-color: #e9ecef; border-color: #dee2e6; }
    .page-item.active .page-link { z-index: 3; color: #fff; background-color: #0d6efd; border-color: #0d6efd; }
    .page-item.disabled .page-link { color: #6c757d; pointer-events: none; background-color: #fff; border-color: #dee2e6; }

    /* ✅ CẤU HÌNH CỘT TIÊU CHÍ (XUỐNG HÀNG) */
    .col-tieuchi {
        max-width: 300px;         /* Giới hạn chiều rộng tối đa là 300px */
        white-space: normal;      /* Cho phép xuống dòng */
        word-wrap: break-word;    /* Ngắt từ nếu từ quá dài */
        overflow-wrap: break-word;
        text-align: left;
    }
</style>

<div class="content"> 
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center border-bottom pb-3">
            <h4 class="text-primary fw-bold">DANH SÁCH PHIẾU YÊU CẦU KIỂM ĐỊNH</h4>
            <button type="button" id="btnMoModal" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus-circle me-2"></i> Lập Phiếu Mới
            </button>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-12 text-end pt-2">
            <span class="small-label">Hiển thị <strong><?php echo count($danhSachPhieu); ?></strong> / Tổng số <strong><?php echo $totalRecords; ?></strong> phiếu</span>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0"> 
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-blue">
                        <tr>
                            <th>Mã Phiếu</th>
                            <th>Ngày Yêu Cầu</th>
                            <th>Người Lập</th>
                            <th>Lô Sản Phẩm</th>
                            <th>Tiêu Chí</th>
                            <th>Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($danhSachPhieu)) {
                            foreach ($danhSachPhieu as $phieu) {
                                $trangThai = $phieu['trangThai'];
                                $badge_class = 'badge-secondary'; 
                                switch ($trangThai) {
                                    case 'Chờ kiểm định': $badge_class = 'badge-warning'; break;
                                    case 'Đang kiểm định': $badge_class = 'badge-info'; break;
                                    case 'Đã hoàn thành': $badge_class = 'badge-success'; break;
                                    case 'Đã hủy': $badge_class = 'badge-danger'; break;
                                    case 'Đạt': $badge_class = 'badge-success'; break;     
                                    case 'Không đạt': $badge_class = 'badge-danger'; break; 
                                }
                        ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($phieu['maPYCKD']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($phieu['ngayLap'])); ?></td>
                            <td><?php echo htmlspecialchars($phieu['nguoiLap']); ?></td>
                            <td><?php echo htmlspecialchars($phieu['tenLo']); ?></td>
                            
                            <td class="col-tieuchi"><?php echo htmlspecialchars($phieu['ghiChu']); ?></td>
                            
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($phieu['trangThai']); ?></span></td>
                        </tr>
                        <?php } } else { ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có dữ liệu phiếu kiểm định.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if($totalPages > 1): ?>
        <div class="card-footer bg-white">
            <nav aria-label="Page navigation">
              <ul class="pagination mb-0">
                <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>">
                    <i class="fas fa-chevron-left me-1"></i> Trước
                  </a>
                </li>
                <li class="page-item disabled">
                    <span class="page-link text-dark">Trang <?php echo $currentPage; ?> / <?php echo $totalPages; ?></span>
                </li>
                <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                  <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>">
                    Sau <i class="fas fa-chevron-right ms-1"></i>
                  </a>
                </li>
              </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="lapPhieuModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document"> 
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-file-medical me-2"></i> LẬP PHIẾU MỚI</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="formLapPYCKD">
          <div class="modal-body">
            <div id="alertMessage" class="alert d-none"></div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>Người lập phiếu:</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($nguoiLapHoTen); ?>" readonly style="background-color: #e9ecef; font-weight: bold; color: #0d6efd;">
                        <input type="hidden" name="nguoiLap" value="<?php echo htmlspecialchars($nguoiLapHoTen); ?>">
                    </div>
                    <div class="mb-3">
                        <label>Ngày lập:</label>
                        <input type="text" class="form-control" value="<?php echo date('d/m/Y'); ?>" readonly style="background-color: #e9ecef;">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-primary">Chọn Lô Sản Phẩm (*):</label>
                        <select class="form-control" name="maLo" required>
                            <option value="">-- Chọn lô --</option>
                            <?php 
                            if (!empty($danhSachLoChoKiemDinh)) {
                                foreach ($danhSachLoChoKiemDinh as $lo) {
                                    $tenSP = isset($lo['tenSP']) ? " - SP: " . $lo['tenSP'] : "";
                                    $label = $lo['tenLo'] . $tenSP . " (SL: " . $lo['soLuong'] . ")";
                                    echo "<option value='" . $lo['maLo'] . "'>" . htmlspecialchars($label) . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <?php if(empty($danhSachLoChoKiemDinh)): ?>
                             <small class="text-danger"><i>Hiện không có lô nào chờ kiểm định.</i></small>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label>Tiêu chí / Ghi chú:</label>
                        <textarea class="form-control" name="ghiChu" rows="3" required placeholder="Nhập nội dung..."></textarea>
                    </div>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy bỏ</button>
            <button type="submit" class="btn btn-primary" id="btnSubmit" <?php echo empty($danhSachLoChoKiemDinh) ? 'disabled' : ''; ?>>
                <i class="fas fa-save me-1"></i> Lập Phiếu
            </button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    $('#btnMoModal').on('click', function(e) { e.preventDefault(); $('#lapPhieuModal').modal('show'); });
    $('.close, .btn-secondary[data-dismiss="modal"]').on('click', function() { $('#lapPhieuModal').modal('hide'); });

    $('#formLapPYCKD').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray(); 
        formData.push({name: 'action', value: 'insert'}); 
        var btn = $('#btnSubmit');
        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Đang xử lý...');
        
        $.ajax({
            type: 'POST', url: 'xuly_pyckd.php', data: formData, dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('✅ Lập phiếu thành công!'); location.reload(); 
                } else {
                    alert('❌ Lỗi: ' + response.error);
                    btn.attr('disabled', false).html('<i class="fas fa-save me-1"></i> Lập Phiếu');
                }
            },
            error: function() {
                alert('❌ Lỗi kết nối Server.');
                btn.attr('disabled', false).html('<i class="fas fa-save me-1"></i> Lập Phiếu');
            }
        });
    });
});
</script>