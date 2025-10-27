<?php
session_start();
require_once("../../class/clslogin.php"); 
$p = new login();
    if (isset($_SESSION['id']) && isset($_SESSION['user']) && isset($_SESSION['pass']) && isset($_SESSION['phanquyen'])) {
        //$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen']);
        if (!$p->confirmlogin($_SESSION['id'], $_SESSION['user'], $_SESSION['pass'], $_SESSION['phanquyen'])) {
            header("Location: ../dangnhap/dangnhap.php"); exit();
        }
    } else {
        header("Location: ../dangnhap/dangnhap.php");
        exit();
    }
?>
<?php 
include_once('../../layout/giaodien/pkh.php'); 
// Đảm bảo đường dẫn file chứa class ketnoi là đúng
include_once('../../class/clsconnect.php'); 

// 1. KHỞI TẠO KẾT NỐI VÀ THU THẬP DỮ LIỆU LỌC
$ketnoiObj = new ketnoi(); 
$conn = $ketnoiObj->connect(); // Lấy đối tượng kết nối MySQLi

// Lấy tham số từ URL (bộ lọc) và làm sạch cơ bản
// Dùng cú pháp isset() thay cho ?? (vì PHP < 7.0)
$maDH = $conn->real_escape_string(isset($_GET['maDH']) ? $_GET['maDH'] : '');
$ngayDat = $conn->real_escape_string(isset($_GET['ngayDat']) ? $_GET['ngayDat'] : '');
$trangThai = $conn->real_escape_string(isset($_GET['trangThai']) ? $_GET['trangThai'] : '');

// 2. XÂY DỰNG MỆNH ĐỀ WHERE
// Dùng cú pháp array() thay cho [] (vì PHP < 5.4)
$where_clauses = array(); 

if (!empty($maDH)) {
    // Tìm kiếm Mã đơn hàng gần đúng (LIKE)
    $where_clauses[] = "maDH LIKE '%{$maDH}%'";
}

if (!empty($ngayDat)) {
    // Tìm kiếm Ngày đặt chính xác (=)
    $where_clauses[] = "ngayDat = '{$ngayDat}'";
}

if (!empty($trangThai)) {
    // Tìm kiếm Trạng thái chính xác (=), loại trừ trường hợp chọn "Tất cả" (value="")
    $where_clauses[] = "trangThai = '{$trangThai}'";
}

// Hợp nhất các điều kiện WHERE
$where = '';
if (count($where_clauses) > 0) {
    $where = ' WHERE ' . implode(' AND ', $where_clauses);
}

// 3. XÂY DỰNG CÂU TRUY VẤN SQL HOÀN CHỈNH VÀ LẤY DỮ LIỆU
$sql = "SELECT maDH, maKH, ngayDat, ngayGiaoDuKien, trangThai, ghiChu 
        FROM DONHANG 
        {$where}
        ORDER BY ngayDat DESC";

// Sử dụng hàm laydulieu() để lấy dữ liệu dạng mảng
$data_donhang = $ketnoiObj->laydulieu($conn, $sql); 

$stt = 1;
?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-primary">
            <i class="bi bi-card-list me-2"></i>Danh sách Đơn hàng
        </h5>
        <button class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Tạo đơn hàng
        </button>
    </div>

    <div class="card p-3 mb-4 shadow-sm">
        <form class="row g-2 align-items-end" method="GET" action="">
            <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">Mã đơn hàng</label>
                <input type="text" class="form-control" placeholder="Nhập mã đơn..." name="maDH" 
                       value="<?php echo htmlspecialchars(isset($_GET['maDH']) ? $_GET['maDH'] : ''); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">Ngày đặt</label>
                <input type="date" class="form-control" name="ngayDat" 
                       value="<?php echo htmlspecialchars(isset($_GET['ngayDat']) ? $_GET['ngayDat'] : ''); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">Trạng thái</label>
                <select class="form-select" name="trangThai">
                    <option value="">Tất cả</option>
                    
                    <?php 
                    $status_options = array('Mới tạo', 'Đang sản xuất', 'Hoàn thành', 'Đã hủy');
                    $current_status = isset($_GET['trangThai']) ? $_GET['trangThai'] : '';

                    foreach ($status_options as $status) {
                        $selected = ($current_status == $status) ? 'selected' : '';
                        echo "<option value=\"{$status}\" {$selected}>{$status}</option>";
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
            <i class="bi bi-list-ul me-2"></i>Danh sách Đơn hàng
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover align-middle m-0 text-center">
                <thead class="thead-blue ">
                    <tr>
                        <th>#</th>
                        <th style="width:10%">Mã DH</th>
                        <th style="width:15%">Ngày đặt</th>
                        <th style="width:15%">Ngày giao DK</th>
                        <th style="width:10%">Trạng thái</th>
                        <th style="width:520px">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Kiểm tra và lặp qua mảng dữ liệu trả về từ laydulieu()
                    if (is_array($data_donhang) && count($data_donhang) > 0) {
                        foreach ($data_donhang as $row) {
                            // Xử lý màu trạng thái (Giữ nguyên logic cũ)
                            $badgeClass = 'bg-secondary';
                            if ($row['trangThai'] == 'Hoàn thành') {
                                $badgeClass = 'bg-success';
                            } elseif ($row['trangThai'] == 'Đang sản xuất') {
                                $badgeClass = 'bg-warning text-dark';
                            } elseif ($row['trangThai'] == 'Mới tạo') {
                                $badgeClass = 'bg-info text-dark';
                            } elseif ($row['trangThai'] == 'Đã hủy') {
                                $badgeClass = 'bg-danger';
                            }

                            echo "<tr>";
                            echo "<td>" . $stt++ . "</td>";
                            echo "<td>DH" . str_pad($row['maDH'], 5, '0', STR_PAD_LEFT) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ngayDat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ngayGiaoDuKien']) . "</td>";
                            echo "<td><span class='badge {$badgeClass}'>" . htmlspecialchars($row['trangThai']) . "</span></td>";
                            echo "<td class='text-start'>" . htmlspecialchars($row['ghiChu']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        // Hiển thị thông báo khi không có dữ liệu hoặc không tìm thấy
                        echo "<tr><td colspan='6' class='text-muted'>Không tìm thấy đơn hàng nào.</td></tr>";
                    }
                    // Đóng kết nối sau khi hoàn tất truy vấn
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

