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

include_once('../../layout/giaodien/qc.php');
include_once('../../class/clsconnect.php');

// Kết nối CSDL
$ketnoiObj = new ketnoi();
$conn = $ketnoiObj->connect();
mysqli_set_charset($conn, "utf8mb4");

// Lấy dữ liệu bộ lọc
$maLo = isset($_GET['maLo']) ? trim($_GET['maLo']) : '';
$ngaySX = isset($_GET['ngaySX']) ? trim($_GET['ngaySX']) : '';

// Xây WHERE
$where_clauses = array();

if (!empty($maLo)) {
    $where_clauses[] = "l.maLo LIKE '%" . $conn->real_escape_string($maLo) . "%'";
}
if (!empty($ngaySX)) {
    $where_clauses[] = "l.ngaySX = '" . $conn->real_escape_string($ngaySX) . "'";
}

$where = (count($where_clauses) > 0) ? (' WHERE ' . implode(' AND ', $where_clauses)) : '';

// truy van sql
$sql = "
SELECT  
    l.maLo,
    l.ngaySX, 
    l.soLuong AS SoLuong,
    l.maSP
FROM 
    losanpham AS l
{$where}
ORDER BY 
    l.ngaySX DESC
";

$data_list = $ketnoiObj->laydulieu($conn, $sql);
$stt = 1;
?>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-primary">
            <i class="bi bi-box-seam me-2"></i>Danh sách lô sản phẩm
        </h5>
    </div>

    <div class="card p-3 mb-4 shadow-sm">
        <form class="row g-2 align-items-end" method="GET" action="">
            <div class="col-md-4">
                <label class="form-label mb-1 fw-semibold">Mã lô</label>
                <input type="text" class="form-control" placeholder="Nhập mã lô..." name="maLo"
                    value="<?php echo htmlspecialchars($maLo); ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label mb-1 fw-semibold">Ngày sản xuất</label>
                <input type="date" class="form-control" name="ngaySX"
                    value="<?php echo htmlspecialchars($ngaySX); ?>">
            </div>

            <div class="col-md-4 d-flex justify-content-start align-items-end">
                <button type="submit" class="btn btn-primary me-2 w-40">
                    <i class="bi bi-search me-1"></i> Tìm
                </button>
                <button type="button" class="btn btn-outline-secondary w-40"
                    onclick="window.location.href=window.location.pathname">
                    <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
                </button>
            </div>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white fw-bold">
            <i class="bi bi-list-ul me-2"></i>Danh sách lô sản phẩm
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover align-middle m-0 text-center">
                <thead class="thead-blue">
                    <tr>
                        <th>#</th>
                        <th>Mã lô</th>
                        <th>Mã SP</th>
                        <th>Ngày sản xuất</th>
                        <th>Số lượng</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (is_array($data_list) && count($data_list) > 0) {
                        foreach ($data_list as $row) {
                            echo "<tr>";
                            echo "<td>" . $stt++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['maLo']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['maSP']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ngaySX']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['SoLuong']) . "</td>";
                            echo "</tr>";

                            // Modal chi tiết
                            echo "
                            <div class='modal fade' id='modal_{$row['maLo']}' tabindex='-1'>
                              <div class='modal-dialog modal-lg modal-dialog-centered'>
                                <div class='modal-content shadow-lg'>
                                  <div class='modal-header bg-primary text-white'>
                                    <h5 class='modal-title'>
                                      <i class='bi bi-info-circle me-2'></i>Chi tiết lô sản phẩm: " . htmlspecialchars($row['maLo']) . "
                                    </h5>
                                    <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                  </div>
                                  <div class='modal-body'>
                                    <div class='p-3 rounded shadow-sm bg-light'>
                                      <div class='row mb-2'>
                                        <div class='col-md-6'><strong>Mã Lô:</strong> " . htmlspecialchars($row['maLo']) . "</div>
                                        <div class='col-md-6'><strong>Mã SP:</strong> " . htmlspecialchars($row['maSP']) . "</div>
                                      </div>
                                      <div class='row mb-2'>
                                        <div class='col-md-6'><strong>Ngày SX:</strong> " . htmlspecialchars($row['ngaySX']) . "</div>
                                        <div class='col-md-6'><strong>Số lượng:</strong> " . htmlspecialchars($row['SoLuong']) . "</div>
                                      </div>
                                    </div>
                                  </div>
                                  <div class='modal-footer'>
                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Đóng</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-muted'>Không tìm thấy lô sản phẩm nào.</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once("../../layout/footer.php"); ?>