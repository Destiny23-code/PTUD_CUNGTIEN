<?php
// Thêm để debug lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once("../../class/clslogin.php");
$p = new login();

// Kiểm tra đăng nhập
if (
  !isset($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen']) ||
  !$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen'])
) {
  header("Location: ../dangnhap/dangnhap.php");
  exit();
}

include_once('../../layout/giaodien/qc.php'); // Sidebar QC
require_once("../../class/clsLapBCCL.php"); // Class LapBCCL

// Nhận dữ liệu từ GET
$maPhieu   = isset($_GET['maPhieu']) ? $_GET['maPhieu'] : '';
$maLo      = isset($_GET['maLo']) ? $_GET['maLo'] : '';
$ngayLap   = isset($_GET['ngayLap']) ? $_GET['ngayLap'] : '';
$tenNV     = isset($_SESSION['hoTen']) ? $_SESSION['hoTen'] : '';
$sDT       = isset($_GET['sDT']) ? $_GET['sDT'] : '';
$ngaySX    = isset($_GET['ngaySX']) ? $_GET['ngaySX'] : '';
$SoLuong   = isset($_GET['SoLuong']) ? $_GET['SoLuong'] : '';
$trangThai = isset($_GET['trangThai']) ? $_GET['trangThai'] : '';
$tieuChi   = isset($_GET['tieuChi']) ? $_GET['tieuChi'] : '';

// Xử lý danh sách tiêu chí
$tieuChiList = preg_split('/[,;\n]+/', $tieuChi, -1, PREG_SPLIT_NO_EMPTY);

// Xử lý POST khi bấm "Lập phiếu"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $ngayLap = isset($_POST['ngayLap']) ? trim($_POST['ngayLap']) : '';
  $nguoiLap = isset($_SESSION['hoTen']) ? trim($_SESSION['hoTen']) : '';
  $maLo = isset($_POST['maLo']) ? trim($_POST['maLo']) : '';
  $maPhieu = isset($_POST['maPhieu']) ? trim($_POST['maPhieu']) : '';
  $tieuChiArr = isset($_POST['tieuChi']) ? $_POST['tieuChi'] : array();
  $ketQuaArr  = isset($_POST['ketQuaTieuChi']) ? $_POST['ketQuaTieuChi'] : array();
  $ketQuaBaoCao = isset($_POST['ketQuaBaoCao']) ? trim($_POST['ketQuaBaoCao']) : '';

  // Kiểm tra các trường bắt buộc
  $hasEmptyField = false;

  if ($ngayLap == '' || $nguoiLap == '' || $maLo == '' || $maPhieu == '') {
    $hasEmptyField = true;
  }

  // Kiểm tra tiêu chí và kết quả
  if (empty($tieuChiArr) || empty($ketQuaArr) || count($tieuChiArr) != count($ketQuaArr)) {
    $hasEmptyField = true;
  }

  // Kiểm tra kết quả tổng thể
  if ($ketQuaBaoCao == '') {
    $hasEmptyField = true;
  }

  if ($hasEmptyField) {
    echo "<script>alert('Bạn chưa nhập đầy đủ thông tin');</script>";
  } else {
    // Kiểm tra logic: nếu có tiêu chí "Không đạt" mà kết quả tổng thể lại chọn "Đạt" thì không hợp lệ
    $coTieuChiKhongDat = false;
    foreach ($ketQuaArr as $ketQua) {
      if (trim($ketQua) === 'Không đạt') {
        $coTieuChiKhongDat = true;
        break;
      }
    }

    if ($coTieuChiKhongDat && $ketQuaBaoCao === 'Đạt') {
      echo "<script>alert('Không thể chọn kết quả tổng thể là Đạt khi có tiêu chí Không đạt!');</script>";
    } elseif ($coTieuChiKhongDat == false && $ketQuaBaoCao === 'Không đạt') {
      echo "<script>alert('Không thể chọn kết quả tổng thể là không đạt khi tất cả tiêu chí đạt!');</script>";
    } else {
      // Nếu dữ liệu hợp lệ, xử lý lưu vào database
      $tieuChiStr = '';
      foreach ($tieuChiArr as $i => $tc) {
        $ketQua = isset($ketQuaArr[$i]) ? $ketQuaArr[$i] : '';
        $tieuChiStr .= trim($tc) . ': ' . $ketQua . '; ';
      }
      $tieuChiStr = rtrim($tieuChiStr, ' ');

      $lapBCCL = new LapBCCL();

      // Kiểm tra phiếu này đã được lập báo cáo hay chưa
      if ($lapBCCL->kiemTraTonTaiBaoCao($maPhieu)) {
        echo "<script>alert('Báo cáo chất lượng này đã tồn tại! Không thể lập thêm.'); window.location.href='./dspyckd.php';</script>";
        exit();
      }

      $insertId = $lapBCCL->insertPhieu($ngayLap, $nguoiLap, $maLo, $maPhieu, $tieuChiStr, $ketQuaBaoCao);

      if ($insertId) {
        $lapBCCL->updateTrangThaiPhieuYCKD($maPhieu, $ketQuaBaoCao);
        $lapBCCL->updateTrangThaiLoSanPham($maLo);
        echo "<script>alert('Lập phiếu thành công!'); window.location.href='./dspyckd.php';</script>";
        exit();
      } else {
        echo "<script>alert('Có lỗi xảy ra khi lập phiếu! Vui lòng kiểm tra kết nối CSDL.');</script>";
      }
    }
  }
}
?>

<div class="content">
  <div class="card shadow-sm p-4">
    <h5 class="fw-bold text-primary mb-4 text-center">LẬP BÁO CÁO CHẤT LƯỢNG</h5>

    <form method="POST">
      <!-- Hidden input cho maNV -->
      <input type="hidden" name="maNV" value="<?php echo htmlspecialchars($_SESSION['id']); ?>" />

      <!-- THÔNG TIN PHIẾU -->
      <div class="form-section bg-light p-3 rounded-3 mb-3 border">
        <div class="section-title fw-bold text-primary mb-2">Thông tin phiếu</div>
        <div class="row">
          <div class="col-md-6 mb-2">
            <label class="form-label fw-bold">Mã phiếu yêu cầu kiểm định</label>
            <input type="text" class="form-control" name="maPhieu" value="<?php echo htmlspecialchars($maPhieu); ?>" readonly />
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label fw-bold">Mã lô sản phẩm</label>
            <input type="text" class="form-control" name="maLo" value="<?php echo htmlspecialchars($maLo); ?>" readonly />
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label fw-bold">Ngày lập phiếu</label>
            <input type="text" class="form-control" name="ngayLap" value="<?php echo htmlspecialchars($ngayLap); ?>" readonly />
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label fw-bold">Người lập phiếu</label>
            <input type="text" class="form-control" name="nguoiLap" value="<?php echo htmlspecialchars($tenNV); ?>" readonly />
          </div>
        </div>
      </div>

      <!-- Hidden inputs tiêu chí -->
      <?php foreach ($tieuChiList as $tc): ?>
        <input type="hidden" name="tieuChi[]" value="<?php echo htmlspecialchars(trim($tc)); ?>">
      <?php endforeach; ?>

      <!-- TIÊU CHÍ KIỂM ĐỊNH -->
      <div class="form-section bg-light p-3 rounded-3 mb-3 border">
        <div class="section-title fw-bold text-primary mb-2">Tiêu chí kiểm định</div>
        <?php foreach ($tieuChiList as $index => $tc): ?>
          <div class="row align-items-center mb-3">
            <div class="col-md-8">
              <label class="form-label"><?php echo ($index + 1) . '. ' . htmlspecialchars(trim($tc)); ?></label>
            </div>
            <div class="col-md-4">
              <select class="form-select" name="ketQuaTieuChi[]" required>
                <option value="">-- Chọn kết quả --</option>
                <option value="Đạt">Đạt</option>
                <option value="Không đạt">Không đạt</option>
              </select>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- KẾT QUẢ BÁO CÁO TỔNG THỂ -->
      <div class="form-section bg-light p-3 rounded-3 mb-3 border">
        <div class="section-title fw-bold text-primary mb-2">Kết quả báo cáo tổng thể</div>
        <div class="row">
          <div class="col-md-12">
            <label class="form-label fw-bold">Kết quả tổng thể của báo cáo chất lượng</label>
            <select class="form-select" name="ketQuaBaoCao" required>
              <option value="">-- Chọn kết quả tổng thể --</option>
              <option value="Đạt">Đạt</option>
              <option value="Không đạt">Không đạt</option>
            </select>
          </div>
        </div>
      </div>

      <!-- NÚT HÀNH ĐỘNG -->
      <div class="text-center mt-3">
        <button class="btn btn-success me-2" type="submit">
          <i class="bi bi-check-circle"></i> Lập phiếu
        </button>
        <a href="./dspyckd.php" class="btn btn-secondary">Hủy</a>
      </div>
    </form>
  </div>
</div>

<?php include_once("../../layout/footer.php"); ?>