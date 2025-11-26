<?php 
include_once("../../layout/giaodien/congnhan.php"); 
include_once("../../class/clsCongNhan.php"); 
include_once("../../class/clsLichLamViec.php"); 
session_start();

// Thông tin nhân viên
$cn = new Congnhan();
$tt = $cn->getTTCN($_SESSION['maNV']); 

$llv = new LichLamViec();
$caList = $llv->getCaLam(); // Lấy ca cố định

// --- Xác định tuần hiển thị dựa trên ngày chọn ---
if(isset($_GET['ngay'])){
    $ngayChon = $_GET['ngay']; // 'YYYY-MM-DD'
} else {
    $ngayChon = date('Y-m-d'); // Mặc định hôm nay
}

// Lấy thứ trong tuần: 1 = Monday, ..., 7 = Sunday
$thuTrongTuan = date('N', strtotime($ngayChon));

// Tính Thứ 2 và Thứ 6 của tuần chứa ngày chọn
$thu2 = date('Y-m-d', strtotime("-".($thuTrongTuan-1)." days", strtotime($ngayChon)));
$thu6 = date('Y-m-d', strtotime("+".(5-$thuTrongTuan)." days", strtotime($ngayChon)));

// Mảng ngày tuần
$days = array();
for($d = strtotime($thu2); $d <= strtotime($thu6); $d = strtotime('+1 day', $d)){
    $days[] = date('Y-m-d', $d);
}

// Lấy ngoại lệ tuần này
$ngoaiLe = $llv->getNgoaiLe($_SESSION['maNV'], $thu2, $thu6);
$llvMap = array();
foreach($ngoaiLe as $nl){
    $llvMap[$nl['ngay']][$nl['maCa']] = $nl;
}

// Mảng tên các ngày Việt Nam
$thuVN = array(
    'Monday'=>'Thứ 2',
    'Tuesday'=>'Thứ 3',
    'Wednesday'=>'Thứ 4',
    'Thursday'=>'Thứ 5',
    'Friday'=>'Thứ 6'
);

// Ngày hôm nay
$today = date('Y-m-d');

// Nút tuần trước / tuần sau / tuần hiện tại
$tuNgayTuanTruoc = date('Y-m-d', strtotime('-7 days', strtotime($thu2)));
$tuNgayTuanSau = date('Y-m-d', strtotime('+7 days', strtotime($thu2)));
$tuNgayHienTai = date('Y-m-d'); // tuần hiện tại
?>

<div class="content">
<h5 class="fw-bold text-primary mb-4">
    <i class="bi bi-calendar3 me-2"></i>Lịch làm việc của tôi
</h5>

<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Lịch làm việc cố định từ <strong>Thứ 2 - Thứ 6</strong>, mỗi ngày có <strong>2 ca</strong>: Sáng (7h-11h) và Chiều (13h-17h).
    Hệ thống chỉ hiển thị <strong>tăng ca</strong> hoặc <strong>nghỉ phép</strong> nếu có thay đổi.
</div>

<!-- Header điều hướng tuần -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <a class="btn btn-outline-primary btn-sm mb-1" href="?ngay=<?php echo $tuNgayTuanTruoc; ?>">
        &lt;&lt; Tuần trước
    </a>

    <div class="text-center p-2 bg-light rounded mb-1 flex-grow-1">
        <strong>Tuần: <?php echo date('d/m/Y', strtotime($thu2)); ?> - <?php echo date('d/m/Y', strtotime($thu6)); ?></strong>
    </div>

    <a class="btn btn-outline-primary btn-sm mb-1" href="?ngay=<?php echo $tuNgayTuanSau; ?>">
        Tuần sau &gt;&gt;
    </a>

    <!-- Nút về tuần hiện tại -->
    <a class="btn btn-outline-success btn-sm ms-2 mb-1" href="?ngay=<?php echo $tuNgayHienTai; ?>">
        Tuần hiện tại
    </a>

    <!-- Form chọn ngày bất kỳ -->
    <form method="get" class="d-flex align-items-center ms-2 mb-1">
        <input type="date" name="ngay" class="form-control form-control-sm me-2" style="width:auto;" value="<?php echo $ngayChon; ?>">
        <button type="submit" class="btn btn-sm btn-success">Xem tuần</button>
    </form>
</div>

<!-- Bảng lịch làm việc -->
<div class="card shadow-sm">
    <div class="card-header bg-secondary text-white fw-bold">
        <i class="bi bi-clock-history me-2"></i>Lịch làm việc tuần <?php echo date('d/m/Y', strtotime($thu2)); ?> - <?php echo date('d/m/Y', strtotime($thu6)); ?>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover align-middle m-0 text-center">
            <thead>
                <tr>
                    <th style="width: 15%;">Ca làm</th>
                    <?php
                    foreach($days as $ngayStr){
                        $thuName = $thuVN[date('l', strtotime($ngayStr))];
                        echo "<th style='width:17%;'>{$thuName} (".date('d/m', strtotime($ngayStr)).")</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($caList as $ca){
                    echo "<tr>";
                    echo "<td class='fw-bold bg-light'>{$ca['tenCa']}<br>
                          <small class='text-muted'>(".substr($ca['gioBatDau'],0,5)." - ".substr($ca['gioKetThuc'],0,5).")</small></td>";

                    foreach($days as $ngayStr){
                        $classToday = ($ngayStr == $today) ? "table-warning" : "";
                        if(isset($llvMap[$ngayStr][$ca['maCa']])){
                            $nl = $llvMap[$ngayStr][$ca['maCa']];
                            if($nl['trangThai']=='Tăng ca'){
                                echo "<td class='{$classToday}'><span class='badge bg-warning text-dark'>Tăng ca</span><br>
                                      <small class='text-danger'>Đến ".substr($nl['gioTangCaKetThuc'],0,5)."</small></td>";
                            } else {
                                echo "<td class='{$classToday}'><span class='badge bg-danger'>Nghỉ phép</span><br>
                                      <small class='text-muted'>({$nl['ghiChu']})</small></td>";
                            }
                        } else {
                            echo "<td class='{$classToday}'><span class='badge bg-success'>Bình thường</span></td>";
                        }
                    }

                    echo "</tr>";
                }
                ?>
            </tbody>
            <tfoot class="bg-light">
                <tr>
                    <td colspan="6" class="text-muted fw-semibold">Thứ 7 & Chủ nhật: Nghỉ theo lịch cố định</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</div>

<?php include_once ("../../layout/footer.php"); ?>
