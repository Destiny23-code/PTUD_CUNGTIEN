<?php
// 1. INCLUDE CÁC FILE CẦN THIẾT
include_once('../../layout/giaodien/qdx.php'); 
include_once('../../class/clsLapPYCKD.php');

// Lấy thông tin người lập từ Session
if (isset($_SESSION['id'])) {
    $nguoiLapMaNV = $_SESSION['id'];
} else {
    $nguoiLapMaNV = 'NV001'; // Giá trị mặc định nếu test chưa login
}

if (isset($_SESSION['hoten'])) {
    $nguoiLapHoTen = $_SESSION['hoten'];
} else {
    $nguoiLapHoTen = 'Nhân viên mẫu'; 
}

// 2. LẤY DỮ LIỆU
$lapPYCKD = new clsLapPYCKD();
// Lấy danh sách các Lô có trạng thái 'Chờ kiểm định'
$danhSachLo = $lapPYCKD->getLoSanPhamByTrangThai('Chờ kiểm định'); 
?>

<div class="content"> 
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center border-bottom pb-3"> 
            <h1 class="text-primary fw-bold fs-3">
                <i class="fas fa-file-alt me-2"></i> LẬP PHIẾU YÊU CẦU KIỂM ĐỊNH
            </h1>
            <a href="danhsach_phieu.php" class="btn btn-secondary btn-lg shadow-sm">
                <i class="fas fa-list me-2"></i> Quay lại Danh sách
            </a>
        </div>
    </div>
    
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 text-dark">
                <i class="fas fa-boxes me-2"></i> Chọn Lô Sản Phẩm để lập phiếu (<?php echo count($danhSachLo); ?> Lô)
            </h5>
        </div>
        
        <div class="card-body p-0"> 
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-blue">
                        <tr>
                            <th>Mã Lô</th>
                            <th>Tên Lô</th>
                            <th>Ngày Sản Xuất</th>
                            <th>Số Lượng</th>
                            <th>Trạng Thái</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($danhSachLo)) {
                            foreach ($danhSachLo as $lo) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($lo['maLo']); ?></td>
                            <td><?php echo htmlspecialchars($lo['tenLo']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($lo['ngaySanXuat'])); ?></td>
                            <td><?php echo number_format($lo['soLuong']); ?></td>
                            <td><span class="badge badge-warning text-dark"><?php echo htmlspecialchars($lo['trangThai']); ?></span></td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm btn-lap-phieu" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#lapPhieuModal"
                                        data-malo="<?php echo htmlspecialchars($lo['maLo']); ?>"
                                        data-tenlo="<?php echo htmlspecialchars($lo['tenLo']); ?>">
                                   <i class="fas fa-plus me-1"></i> Lập Phiếu
                                </button>
                            </td>
                        </tr>
                        <?php 
                            } 
                        } else {
                        ?>
                        <tr>
                            <td colspan="6">
                                <div class="alert alert-info m-4 text-center" role="alert">
                                    <i class="fas fa-info-circle me-2"></i> Hiện tại không có lô sản phẩm nào cần kiểm định.
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lapPhieuModal" tabindex="-1" aria-labelledby="lapPhieuModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="lapPhieuModalLabel"><i class="fas fa-file-medical me-2"></i> LẬP PHIẾU YÊU CẦU KIỂM ĐỊNH</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <form id="formLapPYCKD">
          <div class="modal-body">
            <div id="alertMessage" class="alert d-none"></div>

            <div class="mb-3">
              <label for="maLo_modal" class="form-label fw-bold">Lô Sản Phẩm Yêu Cầu:</label>
              <input type="hidden" name="maLo" id="maLo_modal" required>
              <p class="form-control-plaintext text-danger fw-bold" id="tenLo_display"></p>
            </div>
            
            <div class="mb-3">
              <label for="ngayLap" class="form-label">Ngày Lập:</label>
              <input type="text" class="form-control" id="ngayLap" value="<?php echo date('d/m/Y'); ?>" readonly>
            </div>

            <div class="mb-3">
              <label for="nguoiLap_hoten" class="form-label">Người Lập Phiếu:</label>
              <input type="hidden" name="nguoiLap" id="nguoiLap_manv" value="<?php echo htmlspecialchars($nguoiLapMaNV); ?>" required>
              <input type="text" class="form-control" id="nguoiLap_hoten" value="<?php echo htmlspecialchars($nguoiLapHoTen); ?>" readonly>
            </div>

            <div class="mb-3">
              <label for="ghiChu" class="form-label">Tiêu chí kiểm định (Ghi chú):</label>
              <textarea class="form-control" name="ghiChu" id="ghiChu" rows="3" maxlength="255" placeholder="Nhập các tiêu chí cần kiểm tra..."></textarea>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="submit" class="btn btn-primary" id="btnSubmit">
                <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                Xác Nhận Lập Phiếu
            </button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // 1. Đưa dữ liệu vào Modal khi bấm nút Lập Phiếu
    $('.btn-lap-phieu').on('click', function() {
        var maLo = $(this).attr('data-malo'); 
        var tenLo = $(this).attr('data-tenlo');
        
        $('#maLo_modal').val(maLo);
        $('#tenLo_display').text(tenLo + ' (Mã: ' + maLo + ')');
        $('#ghiChu').val(''); // Xóa ghi chú cũ
        $('#alertMessage').addClass('d-none').removeClass('alert-success alert-danger');
    });

    // 2. Gửi Form bằng Ajax
    $('#formLapPYCKD').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray(); 
        formData.push({name: 'action', value: 'insert'}); 
        
        var btn = $('#btnSubmit');
        btn.attr('disabled', true).find('.spinner-border').removeClass('d-none');
        
        $.ajax({
            type: 'POST',
            url: 'xuly_pyckd.php', 
            data: formData, 
            dataType: 'json',
            success: function(response) {
                var alertDiv = $('#alertMessage');
                alertDiv.removeClass('d-none alert-success alert-danger');

                if (response.success) {
                    alertDiv.addClass('alert-success').text('✅ Lập phiếu thành công!');
                    setTimeout(function() {
                        window.location.href = 'danhsach_phieu.php'; 
                    }, 1500);
                } else {
                    alertDiv.addClass('alert-danger').text(response.error || 'Thất bại.');
                    btn.attr('disabled', false).find('.spinner-border').addClass('d-none');
                }
            },
            error: function() {
                $('#alertMessage').removeClass('d-none').addClass('alert-danger').text('Lỗi kết nối Server.');
                btn.attr('disabled', false).find('.spinner-border').addClass('d-none');
            }
        });
    });
});
</script>