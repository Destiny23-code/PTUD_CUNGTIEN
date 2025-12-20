<?php
// bctk_sanluong.php
// 1. Xử lý năm được chọn từ bộ lọc
$selectedYear = date('Y'); 
if (isset($_GET['year']) && is_numeric($_GET['year'])) {
    $selectedYear = (int)$_GET['year'];
}
// 2. Lấy dữ liệu với năm đã chọn (Sử dụng hàm đã được sửa trong Model)
$sanLuongThangData = $model->getSanLuongTheoThang($selectedYear);
$sanLuongLoaiSPData = $model->thongKeSanLuongTheoLoaiSP($selectedYear); 
// 3. Chuẩn bị dữ liệu cho Chart.js - Sản lượng theo tháng (Biểu đồ Line)
$slThangLabels = array();
$slThangCounts = array();
// Khởi tạo mảng 12 tháng với giá trị 0
$allMonths = array_fill(1, 12, 0); 
foreach ($sanLuongThangData as $row) {
    // Gán sản lượng vào tháng tương ứng
    $allMonths[(int)$row['Thang']] = (int)$row['TongSanLuong'];
}
foreach ($allMonths as $month => $count) {
    $slThangLabels[] = 'T' . $month;
    $slThangCounts[] = $count;
}
$slThangCountsJson = json_encode($slThangCounts); 
// 4. Tạo danh sách các năm để hiển thị bộ lọc (ví dụ: từ năm hiện tại lùi 5 năm)
$availableYears = array();
$currentYear = date('Y');
for ($i = 0; $i < 5; $i++) {
    $availableYears[] = $currentYear - $i;
}
// 5. Chuẩn bị dữ liệu cho Chart.js - Sản lượng theo Loại Sản phẩm (Biểu đồ Bar)
$slSpLabels = array();
$slSpValues = array();
foreach ($sanLuongLoaiSPData as $row) {
    $slSpLabels[] = $row['loaiSP'];
    $slSpValues[] = (int)$row['TongSanLuong'];
}
$slSpLabelsJson = json_encode($slSpLabels);
$slSpValuesJson = json_encode($slSpValues);

?>

<div class="row mb-3">
    <div class="col-12">
        <form method="GET" class="d-flex align-items-center" id="filterYearForm">
            <label for="filterYear" class="form-label m-0 me-2 text-nowrap fw-bold">Chọn Năm báo cáo:</label>
            <select class="form-select form-select-sm" name="year" id="filterYear" onchange="document.getElementById('filterYearForm').submit();">
                <?php foreach ($availableYears as $year): ?>
                    <option value="<?php echo $year; ?>" <?php echo ($selectedYear == $year) ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="type" value="sanluong">
        </form>
    </div>
</div>
<div class="row d-flex align-items-stretch">
    <div class="col-md-6">
        <div class="card p-3 mb-4 shadow-sm h-100">
            <h6 class="fw-bold m-0">Sản lượng theo tháng</h6>
            
            <canvas id="slThangChart"></canvas>
            <div class="text-center mt-2 text-muted small">Năm đang hiển thị: <?php echo $selectedYear; ?></div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card p-3 mb-4 shadow-sm h-100">
            <h6 class="fw-bold">Sản lượng theo loại sản phẩm</h6>
            <canvas id="slXuongChart"></canvas>
        </div>
    </div>
</div>  

<div class="card p-3  shadow-sm mt-3">
    <h6 class="fw-bold">Bảng tổng sản lượng theo loại sản phẩm</h6>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle m-0">
            <thead class="thead-blue text-center">
                <tr>
                    <th>Loại Sản phẩm</th>
                    <th>Tổng Sản lượng (SP)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($sanLuongLoaiSPData) && count($sanLuongLoaiSPData) > 0): ?>
                    <?php foreach ($sanLuongLoaiSPData as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['loaiSP']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['TongSanLuong'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="text-center text-muted">Không có dữ liệu sản lượng.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Biểu đồ Line: Sản lượng theo tháng (Đã có lọc Năm)
var ctxSLThang = document.getElementById('slThangChart');
new Chart(ctxSLThang, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($slThangLabels); ?>,
        datasets: [{
            label: 'Sản lượng năm <?php echo $selectedYear; ?>',
            data: <?php echo $slThangCountsJson; ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.2)',
            fill: true,
            tension: 0.1 // Giữ lại độ cong thấp
        }]
    },
    options: { 
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Tổng Sản lượng (SP)'
                }
            }
        }
    }
});

// Biểu đồ Bar: Sản lượng theo Loại Sản phẩm
var ctxSLXuong = document.getElementById('slXuongChart');
new Chart(ctxSLXuong, {
    type: 'bar',
    data: {
        labels: <?php echo $slSpLabelsJson; ?>,
        datasets: [{
            label: 'Tổng Sản lượng',
            data: <?php echo $slSpValuesJson; ?>,
            backgroundColor: [
                '#198754', 
                '#ffc107', 
                '#dc3545', 
                '#0d6efd', 
                '#6f42c1'  
            ],
            borderWidth: 1
        }]
    },
    options: { 
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
             tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, "."); 
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Tổng Sản lượng (SP)'
                }
            }
        }
    }
});
</script>