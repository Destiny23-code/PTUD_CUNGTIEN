<?php
require_once("../includes/clsconnect.php");
require_once("../includes/clsLapPYCNL.php");

$ketnoi = new ketnoi();
$conn = $ketnoi->connect();
$cls = new clsLapPYCNL($conn);

// --- Lấy danh sách xưởng và kế hoạch sản xuất ---
$listXuong = $ketnoi->laydulieu($conn, "SELECT maXuong, tenXuong FROM xuong");
$listKH = $ketnoi->laydulieu($conn, "SELECT maKHSX, maDH FROM kehoachsanxuat");

$nguyenlieuList = array();
$msg = "";

// --- Khi người dùng chọn xưởng & kế hoạch ---
if (isset($_POST['chon_xuong']) && isset($_POST['chon_kehoach'])) {
    $maXuong = $_POST['chon_xuong'];
    $maKHSX = $_POST['chon_kehoach'];
    $nguyenlieuList = $cls->getNguyenLieuTheoKeHoach($maKHSX, $maXuong);
}

// --- Khi người dùng nhấn "Lập phiếu yêu cầu" ---
if (isset($_POST['btnLapPhieu'])) {
    $nguoiLap = 2; // Demo (quản đốc xưởng Nước)
    $maXuong = $_POST['maXuong'];
    $details = [];

    if (!empty($_POST['soLuongYeuCau'])) {
        foreach ($_POST['soLuongYeuCau'] as $i => $sl) {
            if ($sl > 0) {
                $details[] = [
                    'maKH' => $_POST['maKH'][$i],
                    'maNL' => $_POST['maNL'][$i],
                    'maDC' => $_POST['maDC'][$i],
                    'soLuongYeuCau' => $sl
                ];
            }
        }

        $ok = $cls->insertPhieuYeuCau($nguoiLap, $maXuong, $details);
        $msg = $ok ? "✅ Lập phiếu yêu cầu thành công!" : "❌ Có lỗi khi lập phiếu.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lập phiếu yêu cầu nguyên liệu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3 class="text-primary mb-3"><i class="bi bi-file-earmark-text"></i> LẬP PHIẾU YÊU CẦU NGUYÊN LIỆU</h3>

    <?php if ($msg != ""): ?>
        <div class="alert alert-info"><?= $msg ?></div>
    <?php endif; ?>

    <!-- CHỌN KẾ HOẠCH + XƯỞNG -->
    <form method="post" class="bg-white p-3 rounded shadow-sm mb-4">
        <div class="row">
            <div class="col-md-5">
                <label class="form-label fw-bold">Kế hoạch sản xuất</label>
                <select name="chon_kehoach" class="form-select" required>
                    <option value="">-- Chọn kế hoạch --</option>
                    <?php foreach ($listKH as $kh): ?>
                        <option value="<?= $kh['maKHSX'] ?>"
                            <?= (isset($_POST['chon_kehoach']) && $_POST['chon_kehoach'] == $kh['maKHSX']) ? 'selected' : '' ?>>
                            KHSX #<?= $kh['maKHSX'] ?> (Đơn hàng <?= $kh['maDH'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold">Xưởng sản xuất</label>
                <select name="chon_xuong" class="form-select" required>
                    <option value="">-- Chọn xưởng --</option>
                    <?php foreach ($listXuong as $x): ?>
                        <option value="<?= $x['maXuong'] ?>"
                            <?= (isset($_POST['chon_xuong']) && $_POST['chon_xuong'] == $x['maXuong']) ? 'selected' : '' ?>>
                            <?= $x['tenXuong'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-search"></i> Xem nguyên liệu
                </button>
            </div>
        </div>
    </form>

    <!-- DANH SÁCH NGUYÊN LIỆU -->
    <?php if (!empty($nguyenlieuList)): ?>
    <form method="post" class="bg-white p-3 rounded shadow-sm">
        <input type="hidden" name="maXuong" value="<?= htmlspecialchars($_POST['chon_xuong']) ?>">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-primary text-center">
                <tr>
                    <th>#</th>
                    <th>Tên dây chuyền</th>
                    <th>Tên nguyên liệu</th>
                    <th>Tồn kho</th>
                    <th>Đơn vị</th>
                    <th>Số lượng yêu cầu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nguyenlieuList as $i => $nl): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($nl['tenDC']) ?></td>
                    <td><?= htmlspecialchars($nl['tenNL']) ?></td>
                    <td class="text-end"><?= $nl['soLuongTon'] ?></td>
                    <td class="text-center"><?= $nl['donViTinh'] ?></td>
                    <td>
                        <input type="number" name="soLuongYeuCau[]" class="form-control" min="0" step="0.001">
                        <input type="hidden" name="maKH[]" value="<?= $_POST['chon_kehoach'] ?>">
                        <input type="hidden" name="maNL[]" value="<?= $nl['maNL'] ?>">
                        <input type="hidden" name="maDC[]" value="<?= $nl['maDC'] ?>">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="submit" name="btnLapPhieu" class="btn btn-primary">
            <i class="bi bi-save"></i> Lập phiếu yêu cầu
        </button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
