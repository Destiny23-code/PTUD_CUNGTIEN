<?php
// bctk_chatluong.php

// 1. LẤY DỮ LIỆU THỐNG KÊ CHO BIỂU ĐỒ (Logic cũ)
$tyLeCL = $model->tinhTyLeChatLuong();
$clTong = 0;
$clTyLeDat = 0;
$clTyLeLoi = 0;

if (is_array($tyLeCL) && isset($tyLeCL['Dat']) && is_numeric($tyLeCL['Dat'])) {
    $clTong = (float)$tyLeCL['Dat'] + (float)$tyLeCL['Loi'];
    if ($clTong > 0) {
        $clTyLeDat = ($tyLeCL['Dat'] / $clTong) * 100;
        $clTyLeLoi = ($tyLeCL['Loi'] / $clTong) * 100;
    }
} else {
    $tyLeCL = array('Dat' => 0, 'Loi' => 0);
}

$clLabels = array('Đạt', 'Lỗi');
$clData = array($clTyLeDat, $clTyLeLoi);
$clDataJson = json_encode($clData);

// 2. LẤY PHIẾU BÁO CÁO MỚI NHẤT (Logic Mới)
$latestPhieu = $model->getPhieuBaoCaoMoiNhat();
?>

<div class="row d-flex align-items-stretch">
    
    <div class="col-md-6"> 
        <div class="card p-3 mb-4 shadow-sm h-100 d-flex flex-column">
            <h6 class="fw-bold border-bottom pb-2">Tỷ lệ Chất lượng (Đạt / Lỗi)</h6>
            
            <div class="row flex-grow-1 align-items-center justify-content-center">
                <div class="col-12 text-center" style="height: 250px; position: relative;">
                    <?php if ($clTong > 0): ?>
                        <canvas id="tyLeCLChart"></canvas>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                            Chưa có dữ liệu thống kê.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="row mt-auto pt-3 border-top">
                <div class="col-6 text-center text-success border-end">
                    <span class="fs-4 fw-bold"><?php echo (int) $tyLeCL['Dat']; ?></span><br>
                    <small>Phiếu Đạt</small>
                </div>
                <div class="col-6 text-center text-danger">
                    <span class="fs-4 fw-bold"><?php echo (int) $tyLeCL['Loi']; ?></span><br>
                    <small>Phiếu Lỗi</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card p-3 mb-4 shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h6 class="fw-bold m-0 text-primary"><i class="bi bi-file-earmark-check me-2"></i>Phiếu kiểm định gần nhất</h6>
                <?php if ($latestPhieu): ?>
                    <span class="badge bg-secondary">Mã phiếu: #<?php echo htmlspecialchars($latestPhieu['maPKD']); ?></span>
                <?php endif; ?>
            </div>

            <?php if ($latestPhieu): 
                // Xử lý hiển thị trạng thái dựa trên cột 'ketQuaBaoCao'
                $ketQua = $latestPhieu['ketQuaBaoCao']; //
                $statusClass = ($ketQua == 'Đạt') ? 'success' : 'danger';
                $iconClass = ($ketQua == 'Đạt') ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                
                // Xử lý ngày lập
                $ngayLap = !empty($latestPhieu['ngayLap']) ? date('d/m/Y', strtotime($latestPhieu['ngayLap'])) : 'Chưa cập nhật';
            ?>
                <div class="d-flex flex-column h-100">
                    <div class="alert alert-light border shadow-sm mb-3">
                        <div class="row mb-2">
                            <div class="col-5 fw-bold text-muted">Ngày lập:</div>
                            <div class="col-7"><?php echo $ngayLap; ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 fw-bold text-muted">Người kiểm định:</div>
                            <div class="col-7 text-dark"><?php echo htmlspecialchars($latestPhieu['nguoiLap']); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-5 fw-bold text-muted">Mã Lô hàng:</div>
                            <div class="col-7 fw-bold text-primary">#<?php echo htmlspecialchars($latestPhieu['maLo']); ?></div>
                        </div>
                        <hr class="my-2">
                        <div class="row align-items-center">
                            <div class="col-5 fw-bold text-muted">Kết quả:</div>
                            <div class="col-7">
                                <span class="badge rounded-pill bg-<?php echo $statusClass; ?> fs-6 px-3 py-2">
                                    <i class="bi <?php echo $iconClass; ?> me-1"></i>
                                    <?php echo htmlspecialchars($ketQua); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 flex-grow-1">
                        <label class="fw-bold text-secondary small mb-1">Tiêu chí / Ghi chú:</label>
                        <div class="p-3 bg-light rounded border text-break" style="font-style: italic; min-height: 80px;">
                            <?php 
                                if (!empty($latestPhieu['tieuChi'])) {
                                    echo nl2br(htmlspecialchars($latestPhieu['tieuChi']));
                                } else {
                                    echo '<span class="text-muted">Không có ghi chú chi tiết.</span>';
                                }
                            ?>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                    <div class="text-center">
                        <i class="bi bi-clipboard-x fs-1"></i>
                        <p class="mt-2">Chưa có dữ liệu phiếu báo cáo nào.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
<?php if ($clTong > 0): ?>
var ctxCL = document.getElementById('tyLeCLChart');
new Chart(ctxCL, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($clLabels); ?>,
        datasets: [{
            data: <?php echo $clDataJson; ?>,
            backgroundColor: ['#198754', '#dc3545'], // Xanh cho Đạt, Đỏ cho Lỗi
            borderColor: '#ffffff',
            hoverOffset: 10
        }]
    },
    options: { 
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        var label = context.label || '';
                        if (label) { label += ': '; }
                        if (context.parsed !== null) {
                            label += context.parsed.toFixed(1) + '%';
                        }
                        return label;
                    }
                }
            }
        },
        responsive: true,
        maintainAspectRatio: false, 
    }
});
<?php endif; ?>
</script>