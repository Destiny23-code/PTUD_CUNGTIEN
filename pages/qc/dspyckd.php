<?php
session_start();
require_once("../../class/clslogin.php");
require_once("../../class/clsconnect.php");

$p = new login();

// ✅ Kiểm tra đăng nhập
if (isset($_SESSION['id']) && isset($_SESSION['user']) && isset($_SESSION['pass']) && isset($_SESSION['phanquyen'])) {
    if (!$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen'])) {
        header("Location: ../dangnhap/dangnhap.php");
        exit();
    }
} else {
    header("Location: ../dangnhap/dangnhap.php");
    exit();
}

// ✅ Include layout
include_once('../../layout/giaodien/qc.php');

// ✅ Kết nối CSDL
$ketnoiObj = new ketnoi();
$conn = $ketnoiObj->connect();

if (!$conn) {
    die("<strong>Lỗi kết nối cơ sở dữ liệu:</strong> " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// ✅ Lấy dữ liệu lọc (nếu có)
$maPhieu = isset($_GET['maPhieu']) ? trim($_GET['maPhieu']) : '';
$ngayLap = isset($_GET['ngayLap']) ? trim($_GET['ngayLap']) : '';

// ✅ Xây dựng điều kiện lọc
$where_clauses = array();
if (!empty($maPhieu)) {
    $maPhieu_esc = mysqli_real_escape_string($conn, $maPhieu);
    $where_clauses[] = "p.maPhieu LIKE '%" . $maPhieu_esc . "%'";
}
if (!empty($ngayLap)) {
    $ngayLap_esc = mysqli_real_escape_string($conn, $ngayLap);
    $where_clauses[] = "DATE(p.ngayLap) = '" . $ngayLap_esc . "'";
}
$where = count($where_clauses) > 0 ? "WHERE " . implode(' AND ', $where_clauses) : "";

// ✅ Câu truy vấn SQL
$sql = "
SELECT 
    p.maPhieu, 
    p.ngayLap, 
    p.nguoiLap, 
    p.tieuChi, 
    p.maLo, 
    p.trangThai, 
    l.ngaySX, 
    l.soLuong
FROM 
    phieuyeucaukiemdinh AS p
    LEFT JOIN losanpham AS l ON l.maLo = p.maLo
" . $where . "
ORDER BY p.ngayLap DESC
";

// ✅ Thực thi truy vấn
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("<strong>Lỗi truy vấn SQL:</strong> " . mysqli_error($conn));
}

// ✅ Lấy dữ liệu ra mảng
$data_phieu = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data_phieu[] = $row;
}
$stt = 1;
?>

<!-- Giao diện danh sách -->
<div class="content">
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white fw-bold">
            <i class="bi bi-list-ul me-2"></i>Danh sách Phiếu Yêu cầu Kiểm định
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover align-middle m-0 text-center">
                <thead class="thead-blue">
                    <tr>
                        <th>#</th>
                        <th style="width:15%">Mã Phiếu</th>
                        <th style="width:10%">Mã lô sản phẩm</th>
                        <th style="width:20%">Ngày yêu cầu</th>
                        <th style="width:20%">Người yêu cầu</th>
                        <th style="width:15%">Trạng thái</th>
                        <th style="width:10%">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($data_phieu) && count($data_phieu) > 0): ?>
                        <?php foreach ($data_phieu as $row): ?>
                            <?php
                            // ✅ Màu trạng thái thủ công thay vì match
                            $badgeClass = 'bg-secondary';
                            if ($row['trangThai'] == 'Hoàn thành') $badgeClass = 'bg-success';
                            elseif ($row['trangThai'] == 'Đang kiểm định') $badgeClass = 'bg-warning text-dark';
                            elseif ($row['trangThai'] == 'Chờ kiểm định') $badgeClass = 'bg-info text-dark';
                            elseif ($row['trangThai'] == 'Đã hủy') $badgeClass = 'bg-danger';
                            ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td><?php echo htmlspecialchars($row['maPhieu']); ?></td>
                                <td><?php echo htmlspecialchars($row['maLo']); ?></td>
                                <td><?php echo htmlspecialchars($row['ngayLap']); ?></td>
                                <td><?php echo htmlspecialchars($row['nguoiLap']); ?></td>
                                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['trangThai']); ?></span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#modal_<?php echo htmlspecialchars($row['maPhieu']); ?>">
                                        <i class="bi bi-eye"></i> Xem
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal chi tiết -->
                            <div class="modal fade" id="modal_<?php echo htmlspecialchars($row['maPhieu']); ?>" tabindex="-1" aria-labelledby="label_<?php echo htmlspecialchars($row['maPhieu']); ?>" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content shadow-lg">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="label_<?php echo htmlspecialchars($row['maPhieu']); ?>">
                                                <i class="bi bi-file-earmark-text me-2"></i>Chi tiết phiếu: <?php echo htmlspecialchars($row['maPhieu']); ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                            <div class="p-3 mb-3 rounded shadow-sm" style="background-color:#e7f3ff; border:1px solid #b3d7ff;">
                                                <div class="row mb-2">
                                                    <div class="col-md-6"><strong>Mã Phiếu:</strong> <?php echo htmlspecialchars($row['maPhieu']); ?></div>
                                                    <div class="col-md-6"><strong>Ngày Lập:</strong> <?php echo htmlspecialchars($row['ngayLap']); ?></div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-md-6"><strong>Người Lập:</strong> <?php echo htmlspecialchars($row['tenNV']); ?></div>
                                                    <div class="col-md-6"><strong>Số Điện Thoại:</strong> <?php echo htmlspecialchars($row['sDT']); ?></div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-md-6"><strong>Mã Lô:</strong> <?php echo htmlspecialchars($row['maLo']); ?></div>
                                                    <div class="col-md-6"><strong>Ngày Sản Xuất:</strong> <?php echo htmlspecialchars($row['ngaySX']); ?></div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-md-6"><strong>Số Lượng:</strong> <?php echo htmlspecialchars($row['soLuong']); ?></div>
                                                    <div class="col-md-6"><strong>Trạng Thái:</strong> 
                                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['trangThai']); ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="p-3 rounded shadow-sm" style="background-color:#e7f3ff; border:1px solid #b3d7ff;">
                                                <strong>Tiêu Chí:</strong><br>
                                                <div class="mt-2"><?php echo nl2br(htmlspecialchars($row['tieuChi'])); ?></div>
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <a href="lbccl.php?<?php echo http_build_query(array(
                                                'maPhieu' => $row['maPhieu'],
                                                'ngayLap' => $row['ngayLap'],
                                                'tenNV' => $row['tenNV'],
                                                'sDT' => $row['sDT'],
                                                'maLo' => $row['maLo'],
                                                'ngaySX' => $row['ngaySX'],
                                                'SoLuong' => $row['soLuong'],
                                                'tieuChi' => $row['tieuChi'],
                                                'trangThai' => $row['trangThai']
                                            )); ?>" class="btn btn-success">
                                                <i class="bi bi-file-earmark-plus me-1"></i> Lập báo cáo chất lượng
                                            </a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-muted">Không tìm thấy phiếu yêu cầu kiểm định nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once("../../layout/footer.php"); ?>
