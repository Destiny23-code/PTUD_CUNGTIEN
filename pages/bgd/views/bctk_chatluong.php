<?php
// bctk_chatluong.php

// --- LOGIC XỬ LÝ KHỐI AJAX (GIỮ LẠI NHƯNG KHÔNG CẦN THIẾT NỮA) ---
// Giữ lại khối này nhưng không cần thiết vì không còn card nào gọi nó nữa.
// Tuy nhiên, nếu bạn muốn giữ mã này cho tương lai, đây là phiên bản đã sửa lỗi đường dẫn:

if (isset($_GET['ajax_action']) && $_GET['ajax_action'] == 'get_phieu_detail') {
    // Đường dẫn tuyệt đối an toàn đến thư mục gốc của dự án (PTUD_CUNGTIEN)
    // Giả định cấu trúc: /pages/bgd/views/bctk_chatluong.php (4 cấp từ thư mục gốc)
    $base_dir = dirname(dirname(dirname(dirname(__FILE__))));
    $model_path = $base_dir . '/models/clskehoachsx.php'; 
    
    if (file_exists($model_path)) {
        require_once($model_path); 
    } else {
        echo '<p class="text-danger mt-5 text-center">Lỗi nghiêm trọng: Không tìm thấy file Model tại: ' . htmlspecialchars($model_path) . '</p>';
        exit;
    }
    
    $model = new KeHoachModel(); 
    $maPKD = isset($_GET['maPKD']) ? $_GET['maPKD'] : '';

    // Logic lấy dữ liệu chi tiết phiếu (giữ nguyên để tránh lỗi Fatal Error)
    if (!empty($maPKD) && isset($model)) {
        $phieu = $model->getChiTietPhieuBCCLByMaPKD($maPKD);
        // ... (HTML hiển thị chi tiết nếu cần) ...
    }
    
    // Nếu khối này được gọi, nó sẽ dừng mọi thứ ở đây.
    exit;
}
// --- KẾT THÚC LOGIC XỬ LÝ KHỐI AJAX ---


// --- LOGIC XỬ LÝ KHI TẢI TRANG BÌNH THƯỜNG ---

// Giả định biến $model đã được khởi tạo trước khi file này được include.

// 1. Lấy dữ liệu Tỷ lệ Chất lượng
$tyLeCL = $model->tinhTyLeChatLuong();

// Kiểm tra và gán giá trị mặc định an toàn (Tối ưu hóa PHP 5.2.6)
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

// **LƯU Ý:** // 2. Không cần lấy $dsMaPKD nữa. 
// 3. Các hàm Model liên quan đến tra cứu maPKD không cần gọi nữa.

// --- GIAO DIỆN HIỂN THỊ (HTML/BOOTSTRAP) ---
?>
<div class="row d-flex align-items-stretch">
    
    <div class="col-md-6 offset-md-3"> <div class="card p-3 mb-4 shadow-sm h-100 d-flex flex-column">
            <h6 class="fw-bold">Tỷ lệ Chất lượng (Đạt / Lỗi)</h6>
            
            <div class="row flex-grow-1 align-items-center">
                <div class="col-12 text-center" style="max-height: 350px;">
                    <?php if ($clTong > 0): ?>
                        <canvas id="tyLeCLChart"></canvas>
                    <?php else: ?>
                        <p class="text-muted mt-5">Không có dữ liệu phiếu báo cáo chất lượng.</p>
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

    </div>

<script>
// SCRIPT CHO BIỂU ĐỒ DOUGHNUT (Tỷ lệ Chất lượng)
<?php if ($clTong > 0): ?>
var ctxCL = document.getElementById('tyLeCLChart');
new Chart(ctxCL, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($clLabels); ?>,
        datasets: [{
            data: <?php echo $clDataJson; ?>,
            backgroundColor: ['#198754', '#dc3545'], // Đạt: Xanh, Lỗi: Đỏ
            borderColor: '#ffffff',
            hoverOffset: 10
        }]
    },
    options: { 
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        var label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed !== null) {
                            label += context.parsed.toFixed(1) + '%';
                        }
                        return label;
                    }
                }
            },
            legend: {
                position: 'bottom',
            }
        },
        responsive: true,
        maintainAspectRatio: false, 
    }
});
<?php endif; ?>

</script>