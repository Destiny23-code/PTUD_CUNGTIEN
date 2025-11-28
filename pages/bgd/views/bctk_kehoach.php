<?php
// bctk_kehoach.php (Trong pages/bgd/views/)
// Dùng $model đã được khởi tạo ở bctk.php
$tongQuanPheDuyet = $model->thongKeKHSXPheDuyet(); // SỬ DỤNG HÀM THỐNG KÊ PHÊ DUYỆT
$sanLuongLoaiSP = $model->thongKeSanLuongTheoLoaiSP(); 
$dsKHSX = $model->getDanhSachKeHoachChoBaoCao();

$tongKH = isset($tongQuanPheDuyet['TongKH']) ? (int)$tongQuanPheDuyet['TongKH'] : 0;
$choPheDuyet = isset($tongQuanPheDuyet['ChoPheDuyet']) ? (int)$tongQuanPheDuyet['ChoPheDuyet'] : 0;
$daDuyet = isset($tongQuanPheDuyet['DaDuyet']) ? (int)$tongQuanPheDuyet['DaDuyet'] : 0;
$tuChoi = isset($tongQuanPheDuyet['TuChoi']) ? (int)$tongQuanPheDuyet['TuChoi'] : 0;

// Dữ liệu cho biểu đồ Donut: Chờ duyệt, Đã duyệt, Từ chối
$khStatusData = array($choPheDuyet, $daDuyet, $tuChoi);
$khStatusDataJson = json_encode($khStatusData);

// Dữ liệu cho biểu đồ Phân bổ Kế hoạch theo Loại Sản phẩm
$slSpLabels = array();
$slSpValues = array();
foreach ($sanLuongLoaiSP as $row) {
    $slSpLabels[] = $row['loaiSP'];
    $slSpValues[] = (int)$row['TongSanLuong'];
}
$slSpLabelsJson = json_encode($slSpLabels);
$slSpValuesJson = json_encode($slSpValues);
?>
<style>
    .chart-card-min {
        min-height: 420px;
        display: flex;
        flex-direction: column;
    }
    .chart-container-wrapper {
        flex-grow: 1; 
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px;
    }
    .chart-area {
        width: 100%;
        max-height: 350px;
    }
</style>

<div class="row mb-4">
    <div class="col-md-3">
        <!-- Tổng KH -->
        <div class="card text-center p-3 shadow-sm bg-info-subtle h-100"> 
            <h6 class="text-muted m-0">Tổng KH</h6>
            <h3 class="fw-bold text-info"><?php echo $tongKH; ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <!-- Đã Duyệt -->
        <div class="card text-center p-3 shadow-sm bg-success-subtle h-100">
            <h6 class="text-muted m-0">Đã duyệt</h6>
            <h3 class="fw-bold text-success"><?php echo $daDuyet; ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <!-- Chờ Phê duyệt -->
        <div class="card text-center p-3 shadow-sm bg-warning-subtle h-100">
            <h6 class="text-muted m-0">Chờ phê duyệt</h6>
            <h3 class="fw-bold text-warning"><?php echo $choPheDuyet; ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <!-- Từ chối -->
        <div class="card text-center p-3 shadow-sm bg-danger-subtle h-100">
            <h6 class="text-muted m-0">Từ chối</h6>
            <h3 class="fw-bold text-danger"><?php echo $tuChoi; ?></h3>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card p-3 mb-4 shadow-sm chart-card-min">
            <h6 class="fw-bold">Tỷ lệ phê duyệt</h6>
            <div class="chart-container-wrapper">
                <div class="chart-area" style="max-width: 350px;">
                    <canvas id="khTyLeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3 mb-4 shadow-sm chart-card-min">
            <h6 class="fw-bold">Phân bổ Kế hoạch theo Loại Sản phẩm</h6> 
            <div class="chart-container-wrapper">
                <div class="chart-area">
                    <canvas id="xuongSanLuongChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var currentKHSX = null; 
// Biểu đồ Donut Tỷ lệ phê duyệt
var ctxTyLe = document.getElementById('khTyLeChart');
new Chart(ctxTyLe, {
    type: 'doughnut',
    data: {
        labels: ['Chờ phê duyệt', 'Đã duyệt', 'Từ chối'],
        datasets: [{
            data: <?php echo $khStatusDataJson; ?>,
            backgroundColor: ['#ffc107', '#198754', '#dc3545'], 
        }]
    },
    options: { 
        plugins: {
            legend: {
                position: 'bottom',
            }
        },
        responsive: true,
        maintainAspectRatio: true 
    }
});
// Biểu đồ cột Sản lượng theo Loại Sản phẩm (SỬA maintainAspectRatio thành TRUE)
var ctxXuong = document.getElementById('xuongSanLuongChart');
new Chart(ctxXuong, {
    type: 'bar',
    data: {
        labels: <?php echo $slSpLabelsJson; ?>,
        datasets: [{
            label: 'Số lượng SP theo KH', 
            data: <?php echo $slSpValuesJson; ?>,
            backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545'],
        }]
    },
    options: { 
        plugins: {
            legend: {
                display: false
            }
        },
        responsive: true,
        maintainAspectRatio: true 
    }
});
</script>

<div class="card p-3 shadow-sm mt-3">
    <h6 class="fw-bold">Bảng kế hoạch</h6>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle m-0">
            <thead class="thead-blue text-center">
                <tr>
                    <th>Mã KH</th>
                    <th>Mã ĐH</th>
                    <th>Ngày lập</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($dsKHSX) && count($dsKHSX) > 0): ?>
                    <?php foreach ($dsKHSX as $k): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($k['maKHSX']); ?></td>
                            <td><?php echo htmlspecialchars($k['maDH']); ?></td>
                            <td><?php echo htmlspecialchars($k['ngayLap']); ?></td>
                            <td><?php echo htmlspecialchars($k['trangThai']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">Không tìm thấy kế hoạch nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>