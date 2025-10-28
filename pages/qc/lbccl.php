<?php
session_start();
require_once("../../class/clslogin.php"); 
$p = new login();

// Kiểm tra đăng nhập
if (isset($_SESSION['id']) && isset($_SESSION['user']) && isset($_SESSION['pass']) && isset($_SESSION['phanquyen'])) {
    if (!$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen'])) {
        header("Location: ../dangnhap/dangnhap.php"); exit();
    }
} else {
    header("Location: ../dangnhap/dangnhap.php"); exit();
}

include_once('../../layout/giaodien/qc.php'); // Sidebar QC
include_once('../../class/clsconnect.php'); // Kết nối CSDL

// Nhận dữ liệu từ GET (và gán giá trị mặc định nếu thiếu)
$maPhieu   = isset($_GET['maPhieu']) ? $_GET['maPhieu'] : '';
$maLo      = isset($_GET['maLo']) ? $_GET['maLo'] : '';
$ngayLap   = isset($_GET['ngayLap']) ? $_GET['ngayLap'] : '';
$tenNV     = isset($_GET['tenNV']) ? $_GET['tenNV'] : '';
$sDT       = isset($_GET['sDT']) ? $_GET['sDT'] : '';
$ngaySX    = isset($_GET['ngaySX']) ? $_GET['ngaySX'] : '';
$SoLuong   = isset($_GET['SoLuong']) ? $_GET['SoLuong'] : '';
$trangThai = isset($_GET['trangThai']) ? $_GET['trangThai'] : '';
$tieuChi   = isset($_GET['tieuChi']) ? $_GET['tieuChi'] : '';

// Xử lý danh sách tiêu chí (tách bằng dấu phẩy, chấm phẩy hoặc xuống dòng)
$tieuChiList = preg_split('/[,;\n]+/', $tieuChi, -1, PREG_SPLIT_NO_EMPTY);
?>

<!-- ==================== NỘI DUNG TRANG ==================== -->
<div class="content">
  <div class="card shadow-sm p-4">
    <h5 class="fw-bold text-primary mb-4 text-center">
      LẬP BÁO CÁO CHẤT LƯỢNG
    </h5>

    <!-- THÔNG TIN PHIẾU -->
    <div class="form-section bg-light p-3 rounded-3 mb-3 border">
      <div class="section-title fw-bold text-primary mb-2">Thông tin phiếu</div>
      <div class="row">
        <div class="col-md-6 mb-2">
          <label class="form-label fw-bold">Mã phiếu yêu cầu kiểm định</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($maPhieu); ?>" readonly />
        </div>
        <div class="col-md-6 mb-2">
          <label class="form-label fw-bold">Mã lô sản phẩm</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($maLo); ?>" readonly />
        </div>
        <div class="col-md-6 mb-2">
          <label class="form-label fw-bold">Ngày lập phiếu</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($ngayLap); ?>" readonly />
        </div>
        <div class="col-md-6 mb-2">
          <label class="form-label fw-bold">Người lập phiếu</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($tenNV); ?>" readonly />
        </div>
        <div class="col-md-6 mb-2">
          <label class="form-label fw-bold">Số điện thoại</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($sDT); ?>" readonly />
        </div>
        <div class="col-md-6 mb-2">
          <label class="form-label fw-bold">Trạng thái</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($trangThai); ?>" readonly />
        </div>
      </div>
    </div>

    <!-- THÔNG TIN LÔ SẢN XUẤT -->
    <div class="form-section bg-light p-3 rounded-3 mb-3 border">
      <div class="section-title fw-bold text-primary mb-2">Thông tin lô sản xuất</div>
      <div class="row">
        <div class="col-md-6 mb-2">
          <label class="form-label fw-bold">Ngày sản xuất</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($ngaySX); ?>" readonly />
        </div>
        <div class="col-md-6 mb-2">
          <label class="form-label fw-bold">Số lượng</label>
          <input type="text" class="form-control" value="<?php echo htmlspecialchars($SoLuong); ?>" readonly />
        </div>
      </div>
    </div>

    <!-- TIÊU CHÍ KIỂM ĐỊNH -->
    <div class="form-section bg-light p-3 rounded-3 mb-3 border">
      <div class="section-title fw-bold text-primary mb-2">Tiêu chí kiểm định</div>

      <?php if (!empty($tieuChiList)) : ?>
        <?php foreach ($tieuChiList as $index => $tc) : ?>
          <div class="row align-items-center mb-3">
            <div class="col-md-8">
              <label class="form-label"><?php echo ($index + 1) . '. ' . htmlspecialchars(trim($tc)); ?></label>
            </div>
            <div class="col-md-4">
              <select class="form-select" name="ketQuaTieuChi[]">
                <option value="">-- Chọn kết quả --</option>
                <option value="Đạt">Đạt</option>
                <option value="Không đạt">Không đạt</option>
                <option value="Cần xem xét">Cần xem xét</option>
              </select>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else : ?>
        <p class="text-muted">Không có tiêu chí nào được truyền vào.</p>
      <?php endif; ?>
    </div>

    <!-- KẾT QUẢ KIỂM ĐỊNH -->
    <div class="mb-4 text-center">
      <label class="form-label fw-bold d-block mb-2">Kết quả kiểm định tổng:</label>
      <div class="d-inline-flex justify-content-center" style="gap: 40px">
        <div class="form-check">
          <input class="form-check-input" type="radio" name="trangThaiKiemDinh" id="dat" value="Đạt" />
          <label class="form-check-label" for="dat">Đạt</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="trangThaiKiemDinh" id="khongDat" value="Không đạt" />
          <label class="form-check-label" for="khongDat">Không đạt</label>
        </div>
      </div>
    </div>

    <!-- NÚT HÀNH ĐỘNG -->
    <div class="text-center mt-3">
      <button class="btn btn-success me-2" id="btnDuyet">
        <i class="bi bi-check-circle"></i> Lập phiếu
      </button>
      <a href="./DanhSachPhieuYeuCauKiemDinh.php" class="btn btn-secondary">
        Hủy
      </a>
    </div>
  </div>
</div>

<?php include_once("../../layout/footer.php"); ?>
