<?php
session_start();
require_once("../../class/clslogin.php");
$p = new login();

// Kiểm tra đăng nhập
if (isset($_SESSION['id']) && isset($_SESSION['user']) && isset($_SESSION['pass']) && isset($_SESSION['phanquyen'])) {
    if (!$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen'])) {
        header("Location: ../dangnhap/dangnhap.php");
        exit();
    }
} else {
    header("Location: ../dangnhap/dangnhap.php");
    exit();
}

include_once('../../layout/giaodien/qc.php'); // Sidebar QC
include_once('../../class/clsconnect.php');  // Kết nối CSDL

// 1. KHỞI TẠO KẾT NỐI
$ketnoiObj = new ketnoi();
$conn = $ketnoiObj->connect();
mysqli_set_charset($conn, "utf8mb4");

// 2. LẤY DỮ LIỆU BỘ LỌC (nếu có)
$maPhieu = isset($_GET['maPhieu']) ? trim($_GET['maPhieu']) : '';
$ngayLap = isset($_GET['ngayLap']) ? trim($_GET['ngayLap']) : '';
$trangThai = isset($_GET['trangThai']) ? trim($_GET['trangThai']) : '';

// 3. XÂY DỰNG MỆNH ĐỀ WHERE
$where_clauses = array();
if (!empty($maPhieu)) {
    $where_clauses[] = "p.maPhieu LIKE '%" . $conn->real_escape_string($maPhieu) . "%'";
}
if (!empty($ngayLap)) {
    $where_clauses[] = "p.ngayLap = '" . $conn->real_escape_string($ngayLap) . "'";
}

// ✅ Bổ sung lọc trạng thái
if (!empty($trangThai)) {
    $where_clauses[] = "p.trangThai = N'" . $conn->real_escape_string($trangThai) . "'";
}

$where = (count($where_clauses) > 0) ? (' WHERE ' . implode(' AND ', $where_clauses)) : '';

// 4. CÂU TRUY VẤN SQL
$sql = "
SELECT 
    p.maPhieu, 
    p.ngayLap, 
    p.nguoiLap, 
    s.tieuChiKiemDinh as tieuChi, 
    p.maLo, 
    p.trangThai,
    l.ngaySX, 
    l.soLuong AS SoLuong,
    l.maSP
FROM losanpham AS l
    INNER JOIN phieuyeucaukiemdinh AS p ON l.maLo = p.maLo
    INNER JOIN sanpham AS s ON l.maSP = s.maSP
{$where}
ORDER BY 
    p.ngayLap DESC
";

$data_phieu = $ketnoiObj->laydulieu($conn, $sql);
$stt = 1;
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-primary">
            <i class="bi bi-card-list me-2"></i>Danh sách phiếu yêu cầu kiểm định
        </h5>
    </div>

    <div class="card p-3 mb-4 shadow-sm">
        <form class="row g-2 align-items-end" method="GET" action="">
            <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">Mã phiếu</label>
                <input type="text" class="form-control" placeholder="Nhập mã đơn..." name="maPhieu"
                    value="<?php echo htmlspecialchars($maPhieu); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">Ngày đặt</label>
                <input type="date" class="form-control" name="ngayLap"
                    value="<?php echo htmlspecialchars($ngayLap); ?>">
            </div>

            <!-- ✅ Thêm chọn trạng thái -->
            <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">Trạng thái</label>
                <select class="form-select" name="trangThai">
                    <option value="">Tất cả</option>
                    <?php
                    $status_options = array('Đạt', 'Không đạt', 'Chờ kiểm định');
                    foreach ($status_options as $status) {
                        $selected = ($trangThai == $status) ? 'selected' : '';
                        echo "<option value=\"$status\" $selected>$status</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3 d-flex justify-content-start align-items-end">
                <button type="submit" class="btn btn-primary me-2 w-30">
                    <i class="bi bi-search me-1"></i> Tìm
                </button>
                <button type="button" class="btn btn-outline-secondary w-40" onclick="window.location.href=window.location.pathname">
                    <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
                </button>
            </div>
        </form>
    </div>

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
                    <?php
                    if (is_array($data_phieu) && count($data_phieu) > 0) {
                        foreach ($data_phieu as $row) {
                            // Màu trạng thái
                            $badgeClass = 'bg-secondary';
                            if ($row['trangThai'] == 'Đạt') {
                                $badgeClass = 'bg-success';
                            } elseif ($row['trangThai'] == 'Đang kiểm định') {
                                $badgeClass = 'bg-warning text-dark';
                            } elseif ($row['trangThai'] == 'Chờ kiểm định') {
                                $badgeClass = 'bg-info text-dark';
                            } elseif ($row['trangThai'] == 'Không đạt') {
                                $badgeClass = 'bg-danger';
                            }

                            // Hàng bảng
                            echo "<tr>";
                            echo "<td>" . $stt++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['maPhieu']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['maLo']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ngayLap']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nguoiLap']) . "</td>";
                            echo "<td><span class='badge {$badgeClass}'>" . htmlspecialchars($row['trangThai']) . "</span></td>";
                            echo "<td>
                                    <button type='button' class='btn btn-sm btn-outline-primary' 
                                        data-bs-toggle='modal' data-bs-target='#modal_{$row['maPhieu']}'>
                                        <i class='bi bi-eye'></i> Xem
                                    </button>
                                  </td>";
                            echo "</tr>";

                            // Modal chi tiết
                            echo "
                            <div class='modal fade' id='modal_{$row['maPhieu']}' tabindex='-1' aria-labelledby='label_{$row['maPhieu']}' aria-hidden='true'>
                              <div class='modal-dialog modal-lg modal-dialog-centered'>
                                <div class='modal-content shadow-lg'>
                                  <div class='modal-header bg-primary text-white'>
                                    <h5 class='modal-title' id='label_{$row['maPhieu']}'>
                                      <i class='bi bi-file-earmark-text me-2'></i>Chi tiết phiếu: " . htmlspecialchars($row['maPhieu']) . "
                                    </h5>
                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                  </div>
                                  <div class='modal-body'>

                                    <!-- Khối thông tin chung -->
                                    <div class='p-3 mb-3 rounded shadow-sm' 
                                         style='background-color: #e7f3ff; border: 1px solid #b3d7ff;'>
                                      <div class='row mb-2'>
                                        <div class='col-md-6'><strong>Mã Phiếu:</strong> " . htmlspecialchars($row['maPhieu']) . "</div>
                                        <div class='col-md-6'><strong>Ngày Lập:</strong> " . htmlspecialchars($row['ngayLap']) . "</div>
                                      </div>
                                      <div class='row mb-2'>
                                        <div class='col-md-6'><strong>Người Lập:</strong> " . htmlspecialchars($row['nguoiLap']) . "</div>
                                        <div class='col-md-6'><strong>Mã sản phẩm:</strong> " . htmlspecialchars($row['maSP']) . "</div>
                                      </div>
                                      <div class='row mb-2'>
                                        <div class='col-md-6'><strong>Mã Lô:</strong> " . htmlspecialchars($row['maLo']) . "</div>
                                        <div class='col-md-6'><strong>Ngày Sản Xuất:</strong> " . htmlspecialchars($row['ngaySX']) . "</div>
                                      </div>
                                      <div class='row mb-2'>
                                        <div class='col-md-6'><strong>Số Lượng:</strong> " . htmlspecialchars($row['SoLuong']) . "</div>
                                        <div class='col-md-6'><strong>Trạng Thái:</strong> 
                                          <span class='badge {$badgeClass}'>" . htmlspecialchars($row['trangThai']) . "</span>
                                        </div>
                                      </div>
                                    </div>

                                    <!-- Khối tiêu chí -->
                                    <div class='p-3 rounded shadow-sm' 
                                         style='background-color: #e7f3ff; border: 1px solid #b3d7ff;'>
                                      <strong>Tiêu Chí:</strong><br>
                                      <div class='mt-2'>
                                        " . nl2br(str_replace('.', ".\n", htmlspecialchars($row['tieuChi']))) . "
                                      </div>
                                    </div>

                                  </div>
                                  <div class='modal-footer'>
                                    <a href='lbccl.php?"
                                . "maPhieu=" . urlencode($row['maPhieu'])
                                . "&ngayLap=" . urlencode($row['ngayLap'])
                                . "&tenNV=" . urlencode($row['nguoiLap'])
                                . "&sDT=" . urlencode($row['maSP'])
                                . "&maLo=" . urlencode($row['maLo'])
                                . "&ngaySX=" . urlencode($row['ngaySX'])
                                . "&SoLuong=" . urlencode($row['SoLuong'])
                                . "&tieuChi=" . urlencode($row['tieuChi'])
                                . "&trangThai=" . urlencode($row['trangThai'])
                                . "' class='btn btn-success'>
                                        <i class='bi bi-file-earmark-plus me-1'></i> Lập báo cáo chất lượng
                                    </a>
                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Đóng</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-muted'>Không có phiếu yêu cầu kiểm định.</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once("../../layout/footer.php"); ?>