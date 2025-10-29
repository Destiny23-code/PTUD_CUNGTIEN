<?php
// Bao gồm session, header, và class như trên
include_once('../../layout/giaodien/qc.php');
include_once('../../class/clsLapPYCKD.php');

$model = new clsLapPYCKD(new PDO('mysql:host=localhost;dbname=qlsx', 'root', ''));
$trangThai = isset($_GET['trangThai']) ? $_GET['trangThai'] : '';
$danhsach_lo = $trangThai ? $model->getLoSanPhamByTrangThai($trangThai) : $model->getLoSanPham();
?>

<div class="content">
    <h5 class="fw-bold text-primary"><i class="bi bi-box-seam me-2"></i>Danh sách Lô Sản phẩm</h5>
    <div class="card p-3 mb-4 shadow-sm">
        <form method="GET">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="trangThai">
                        <option value="">Tất cả</option>
                        <option value="Chờ kiểm định" <?php echo ($trangThai == 'Chờ kiểm định') ? 'selected' : ''; ?>>Chờ kiểm định</option>
                        <option value="Đang kiểm định" <?php echo ($trangThai == 'Đang kiểm định') ? 'selected' : ''; ?>>Đang kiểm định</option>
                        <!-- Thêm các trạng thái khác -->
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                </div>
            </div>
        </form>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="thead-blue">
                    <tr>
                        <th>#</th>
                        <th>Mã lô</th>
                        <th>Tên lô</th>
                        <th>Ngày sản xuất</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stt = 1;
                    if (is_array($danhsach_lo) && count($danhsach_lo) > 0) {
                        foreach ($danhsach_lo as $row) {
                            echo "<tr>";
                            echo "<td>" . $stt++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['maLo']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['tenLo']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ngaySanXuat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['soLuong']) . "</td>";
                            echo "<td><span class='badge bg-warning'>" . htmlspecialchars($row['trangThai']) . "</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-muted'>Không có lô sản phẩm nào.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include_once("../../layout/footer.php"); ?>