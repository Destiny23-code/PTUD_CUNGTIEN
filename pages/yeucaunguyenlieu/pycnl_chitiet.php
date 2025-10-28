<?php
require_once("../includes/clsconnect.php");
require_once("../includes/clsLapPYCNL.php");

$ketnoi = new ketnoi();
$conn = $ketnoi->connect();
$cls = new clsLapPYCNL($conn);

$maPYCNL = isset($_GET['id']) ? intval($_GET['id']) : 0;
$chiTiet = $cls->getChiTietPhieu($maPYCNL);

// Lấy thông tin chung phiếu
$infoSql = "SELECT p.maPYCNL, p.ngayLap, p.trangThai, n.hoTen AS tenNV, x.tenXuong
            FROM phieuyeucaunguyenlieu p
            JOIN nhanvien n ON p.nguoiLap = n.maNV
            JOIN xuong x ON p.maXuong = x.maXuong
            WHERE p.maPYCNL = $maPYCNL";
$info = $ketnoi->laydulieu($conn, $infoSql);
$phieu = !empty($info) ? $info[0] : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chi tiết phiếu yêu cầu nguyên liệu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <a href="pycnl_danhsach.php" class="btn btn-secondary mb-3">
    <i class="bi bi-arrow-left"></i> Quay lại danh sách
  </a>

  <?php if ($phieu): ?>
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Phiếu yêu cầu #<?= $phieu['maPYCNL'] ?></h5>
    </div>
    <div class="card-body">
      <p><strong>Ngày lập:</strong> <?= $phieu['ngayLap'] ?: '—' ?></p>
      <p><strong>Người lập:</strong> <?= htmlspecialchars($phieu['tenNV']) ?></p>
      <p><strong>Xưởng:</strong> <?= htmlspecialchars($phieu['tenXuong']) ?></p>
      <p><strong>Trạng thái:</strong> <?= htmlspecialchars($phieu['trangThai']) ?></p>

      <hr>
      <h6 class="text-primary mb-3"><i class="bi bi-box-seam"></i> Danh sách nguyên liệu yêu cầu</h6>

      <table class="table table-bordered table-striped align-middle">
        <thead class="table-primary text-center">
          <tr>
            <th>#</th>
            <th>Dây chuyền</th>
            <th>Nguyên liệu</th>
            <th>Sản phẩm (kế hoạch)</th>
            <th>Số lượng yêu cầu</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($chiTiet)): ?>
            <?php foreach ($chiTiet as $i => $c): ?>
            <tr>
              <td class="text-center"><?= $i + 1 ?></td>
              <td><?= htmlspecialchars($c['tenDC']) ?></td>
              <td><?= htmlspecialchars($c['tenNL']) ?></td>
              <td><?= htmlspecialchars($c['tenSP']) ?></td>
              <td class="text-end"><?= number_format($c['soLuongYeuCau'], 3) ?></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted">Không có dữ liệu chi tiết</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php else: ?>
    <div class="alert alert-danger">Không tìm thấy phiếu yêu cầu.</div>
  <?php endif; ?>
</div>
</body>
</html>
