<?php
// bctk_kehoach.php (Trong pages/bgd/views/)
// Dùng $model đã được khởi tạo ở bctk.php
$tongQuanPheDuyet = $model->thongKeKHSXPheDuyet(); 
$dsKHSX = $model->getDanhSachKeHoachChoBaoCao();

$tongKH = isset($tongQuanPheDuyet['TongKH']) ? (int)$tongQuanPheDuyet['TongKH'] : 0;
$choPheDuyet = isset($tongQuanPheDuyet['ChoPheDuyet']) ? (int)$tongQuanPheDuyet['ChoPheDuyet'] : 0;
$daDuyet = isset($tongQuanPheDuyet['DaDuyet']) ? (int)$tongQuanPheDuyet['DaDuyet'] : 0;
$tuChoi = isset($tongQuanPheDuyet['TuChoi']) ? (int)$tongQuanPheDuyet['TuChoi'] : 0;

// Dữ liệu cho biểu đồ Donut: Chờ duyệt, Đã duyệt, Từ chối
$khStatusData = array($choPheDuyet, $daDuyet, $tuChoi);
$khStatusDataJson = json_encode($khStatusData);
?>
<style>
    /* 💥 ĐÃ CHUYỂN TỪ min-height SANG height ĐỂ ÉP BUỘC CHIỀU CAO BẰNG NHAU */
    .chart-card-min, .table-card-min {
        height: 420px; 
        display: flex;
        flex-direction: column;
        margin-bottom: 1.5rem !important; /* Đảm bảo khoảng cách dưới */
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
    /* Thêm style để bảng kế hoạch có thể mở rộng hết chiều cao card */
    .table-responsive-flex {
        flex-grow: 1;
        overflow-y: auto; 
    }

    /* 🎨 CSS ĐỂ LÀM GỌN SUMMARY CARDS (GIỐNG HÌNH ẢNH) */
    .summary-box {
        padding: 10px;
        height: 70px; /* Chiều cao cố định cho Summary Box */
        border-radius: 0.25rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        align-items: center;
        text-align: center;
    }
    .summary-text {
        font-size: 0.9rem;
        margin: 0;
        font-weight: 500;
    }
    .summary-number {
        font-size: 1.8rem;
        font-weight: bold;
        line-height: 1;
        margin-top: 5px;
    }
    /* Màu cho summary cards */
    .bg-custom-blue { background-color: #e0f7fa; color: #00bcd4; border: 1px solid #b2ebf2; } 
    .bg-custom-green { background-color: #e8f5e9; color: #4caf50; border: 1px solid #c8e6c9; } 
    .bg-custom-yellow { background-color: #fffde7; color: #ffeb3b; border: 1px solid #fff59d; } 
    .bg-custom-red { background-color: #ffebee; color: #f44336; border: 1px solid #ffcdd2; } 
    .text-blue { color: #00bcd4 !important; }
    .text-green { color: #4caf50 !important; }
    .text-yellow { color: #ffc107 !important; }
    .text-red { color: #f44336 !important; }
    /* Giả định màu cho thead-blue */
    .thead-blue { background-color: #0d6efd; color: white; }

    /* Thêm padding dưới cho card bảng để phân trang không bị sát mép */
    .table-card-min .card-body {
        padding-bottom: 0.5rem; 
    }

</style>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="summary-box bg-custom-blue"> 
            <p class="summary-text text-info">Tổng KH</p>
            <span class="summary-number text-blue"><?php echo $tongKH; ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-box bg-custom-green">
            <p class="summary-text text-success">Đã duyệt</p>
            <span class="summary-number text-green"><?php echo $daDuyet; ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-box bg-custom-yellow">
            <p class="summary-text text-warning">Chờ phê duyệt</p>
            <span class="summary-number text-yellow"><?php echo $choPheDuyet; ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-box bg-custom-red">
            <p class="summary-text text-danger">Từ chối</p>
            <span class="summary-number text-red"><?php echo $tuChoi; ?></span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card p-3 shadow-sm chart-card-min">
            <h6 class="fw-bold">Tỷ lệ phê duyệt</h6>
            <div class="chart-container-wrapper">
                <div class="chart-area" style="max-width: 350px;">
                    <canvas id="khTyLeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3 shadow-sm table-card-min">
            <h6 class="fw-bold">Bảng kế hoạch</h6>
            <div class="table-responsive table-responsive-flex">
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
            
            <div class="d-flex justify-content-end mt-2">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm m-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
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

</script>