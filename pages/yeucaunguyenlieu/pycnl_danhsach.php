<?php
require_once("../includes/clsconnect.php");
require_once("../includes/clsLapPYCNL.php");

$ketnoi = new ketnoi();
$conn = $ketnoi->connect();
$cls = new clsLapPYCNL($conn);

// Lấy danh sách phiếu từ CSDL
$listPhieu = $cls->getAllPhieuYeuCau();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Danh sách phiếu yêu cầu nguyên liệu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h3 class="text-primary mb-3">
    <i class="bi bi-list-task"></i> DANH SÁCH PHIẾU YÊU CẦU NGUYÊN LIỆU
  </h3>

  <a href="pycnl.php" class="btn btn-success mb-3">
    <i class="bi bi-plus-circle"></i> Lập phiếu mới
  </a>

  <table class="table table-bordered table-striped shadow-sm bg-white align-middle">
    <thead class="table-primary text-center">
      <tr>
        <th>#</th>
        <th>Ngày lập</th>
        <th>Người lập</th>
        <th>Xưởng</th>
        <th>Trạng thái</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($listPhieu)): ?>
        <?php foreach ($listPhieu as $i => $p): ?>
        <tr>
          <td class="text-center"><?= $i + 1 ?></td>
          <td class="text-center"><?= $p['ngayLap'] ?? '—' ?></td>
          <td><?= htmlspecialchars($p['nguoiLap']) ?></td>
          <td><?= htmlspecialchars($p['tenXuong']) ?></td>
          <td class="text-center">
            <?php
              $badgeClass = match($p['trangThai']) {
                'Chờ duyệt' => 'bg-warning text-dark',
                'Đã duyệt' => 'bg-success',
                'Đã cấp' => 'bg-info text-dark',
                default => 'bg-secondary'
              };
            ?>
            <span class="badge <?= $badgeClass ?>"><?= $p['trangThai'] ?></span>
          </td>
          <td class="text-center">
            <a href="pycnl_chitiet.php?id=<?= $p['maPYCNL'] ?>" class="btn btn-outline-primary btn-sm">
              <i class="bi bi-eye"></i> Xem chi tiết
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center text-muted">Không có phiếu yêu cầu nào</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
