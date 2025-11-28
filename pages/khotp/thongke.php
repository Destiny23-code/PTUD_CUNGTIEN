<?php
include_once('../../layout/giaodien/khotp.php');
require_once('../../class/clsconnect.php');

// SỬA: Kết nối ổn định
$ketnoi_instance = new ketnoi();
$conn = $ketnoi_instance->connect();

// --- Dữ liệu ---
$sql = "SELECT COUNT(maSP) as tong_sp, SUM(soLuongTon) as tong_ton, AVG(soLuongTon) as tb_ton FROM sanpham";
$stats = $conn->query($sql)->fetch_assoc();

$sql_list = "SELECT maSP, tenSP, donViTinh, soLuongTon FROM sanpham ORDER BY soLuongTon DESC";
$sanpham_list = $conn->query($sql_list)->fetch_all(MYSQLI_ASSOC);

// Nhập xuất
$nhapxuat_sql = "SELECT MONTH(ngayNhap) as thang,
    SUM(nhap) as nhap_kho, COALESCE(SUM(xuat),0) as xuat_kho
FROM (
    SELECT pnk.ngayNhap, SUM(cpnk.soLuongNhap) as nhap, 0 as xuat
    FROM phieunhapkho pnk LEFT JOIN chitiet_phieunhapkho cpnk ON pnk.maPNK=cpnk.maPNK
    WHERE pnk.ngayNhap IS NOT NULL AND YEAR(pnk.ngayNhap)=2025 GROUP BY pnk.maPNK
    UNION ALL
    SELECT dh.ngayDat, 0, SUM(ctdh.soLuong)
    FROM donhang dh JOIN chitiet_donhang ctdh ON dh.maDH=ctdh.maDH
    WHERE dh.ngayDat IS NOT NULL AND YEAR(dh.ngayDat)=2025 AND dh.trangThai IN ('Hoàn thành','Đang sản xuất')
    GROUP BY dh.maDH
) t GROUP BY thang ORDER BY thang";
$nx_data = array_fill(1,12,[ 'nhap_kho'=>0, 'xuat_kho'=>0 ]);
foreach ($conn->query($nhapxuat_sql) as $r) $nx_data[$r['thang']] = $r;

// Phân loại tồn kho + danh sách chi tiết
$con_nhieu = $sap_het = $het_hang = [];
$con = $sap = $het = 0;
foreach($sanpham_list as $sp){
    if($sp['soLuongTon'] == 0){
        $het++; $het_hang[] = $sp;
    } elseif($sp['soLuongTon'] < 50){
        $sap++; $sap_het[] = $sp;
    } else {
        $con++; $con_nhieu[] = $sp;
    }
}
$co_canh_bao = ($het + $sap > 0);

function e($v){ return htmlspecialchars($v??'', ENT_QUOTES, 'UTF-8'); }
?>

<style>
.stat-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    padding: 30px 20px;
    text-align: center;
    transition: all .3s;
    cursor: pointer;
    height: 100%
}

.stat-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15)
}

.stat-card.warning {

    background: linear-gradient(135deg, #fff 0%, #fff8f8 100%)
}

.stat-card .icon {
    font-size: 52px;
    margin-bottom: 15px
}

.text-sp {
    color: #0d6efd
}

.text-ton {
    color: #0dcaf0
}

.text-tb {
    color: #28a745
}

.text-warning {
    color: #dc3545;
    font-weight: 800
}

.modal-header .btn-close {
    margin: -1rem -1rem -1rem auto
}

table,
th,
td {
    border: 1px solid black;
    border-collapse: collapse;
}
</style>

<div class="content">
    <div class="card shadow-sm p-4">
        <h3 class="text-primary fw-bold mb-4"><i class="bi bi-box-seam-fill me-2"></i> Thống kê tồn kho thành phẩm</h3>

        <!-- 4 CARD ĐẸP NHƯ ẢNH -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card" data-bs-toggle="modal" data-bs-target="#modalTonKho">
                    <div class="icon text-primary">📦</div>
                    <p class="mb-1 text-muted small">Tổng sản phẩm</p>
                    <h2 class="text-sp fw-bold"><?=e($stats['tong_sp'])?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" data-bs-toggle="modal" data-bs-target="#modalTonKho">
                    <div class="icon text-ton">📊</div>
                    <p class="mb-1 text-muted small">Tổng tồn kho</p>
                    <h2 class="text-ton fw-bold"><?=number_format($stats['tong_ton'])?> <small class="fs-5">SP</small>
                    </h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card <?= $co_canh_bao ? 'warning' : '' ?>" data-bs-toggle="modal"
                    data-bs-target="#modalCanhBao">
                    <div class="icon <?= $co_canh_bao ? 'text-warning' : 'text-success' ?>">⚠️</div>
                    <p class="mb-1 text-muted small">Cảnh báo tồn kho</p>
                    <h2 class="text-warning fw-bold"><?=$het?> hết / <?=$sap?> sắp hết</h2>
                    <?php if($co_canh_bao): ?><small class="text-danger"><i></i>
                    </small><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- BIỂU ĐỒ NHẬP XUẤT -->
        <div class="card shadow-sm p-4">
            <h5 class="text-secondary mb-3"><i class="bi bi-bar-chart-line-fill me-2"></i> Nhập - Xuất kho theo tháng
                (2025)</h5>
            <canvas id="chartNhapXuat" height="400"></canvas>
        </div>
    </div>
</div>

<!-- MODAL CHI TIẾT TỒN KHO - ĐÃ FIX CĂN CHỈNH -->
<div class="modal fade" id="modalTonKho" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title"><i class="bi bi-table me-2"></i> Chi tiết tồn kho</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Mã SP</th>
                                <th>Tên sản phẩm</th>
                                <th>Đơn vị</th>
                                <th>Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($sanpham_list as $sp): ?>
                            <tr>
                                <td class="fw-bold"><?=e($sp['maSP'])?></td>
                                <td class="text-start"><?=e($sp['tenSP'])?></td>
                                <td><?=e($sp['donViTinh'])?></td>
                                <td
                                    class="fw-bold <?= $sp['soLuongTon']==0?'text-danger':($sp['soLuongTon']<50?'text-warning':'text-success') ?>">
                                    <?=number_format($sp['soLuongTon'])?>
                                    <?php if($sp['soLuongTon']==0): ?><span class="badge bg-danger ms-2">Hết</span>
                                    <?php elseif($sp['soLuongTon']<50): ?><span
                                        class="badge bg-warning text-dark ms-2">Sắp hết</span><?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PHÂN LOẠI TỒN KHO - NHỎ GỌN + CHI TIẾT -->
<div class="modal fade" id="modalCanhBao" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h4 class="modal-title"><i class="bi bi-pie-chart-fill me-2"></i> Phân loại tồn kho</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <canvas id="chartPie" height="280"></canvas>
                    </div>
                    <div class="col-md-7">
                        <h5 class="text-center mb-3">Chi tiết từng nhóm</h5>
                        <!-- Còn nhiều -->
                        <div class="mb-3">
                            <h6><span class="badge bg-success me-2">Còn nhiều (≥50)</span> <?=count($con_nhieu)?> sản
                                phẩm</h6>
                            <div style="max-height:120px; overflow-y:auto; font-size:0.9rem;">
                                <?php foreach($con_nhieu as $sp): ?>
                                <div><strong><?=e($sp['maSP'])?></strong> - <?=e($sp['tenSP'])?>
                                    (<?=number_format($sp['soLuongTon'])?>)</div>
                                <?php endforeach; ?>
                                <?php if(empty($con_nhieu)) echo "<em>Không có</em>"; ?>
                            </div>
                        </div>
                        <!-- Sắp hết -->
                        <div class="mb-3">
                            <h6><span class="badge bg-warning text-dark me-2">Sắp hết (<50) <?=count($sap_het)?> sản
                                        phẩm</h6>
                                        <div
                                            style="max-height:100px; overflow-y:auto; font-size:0.9rem; color:#e67e22;">
                                            <?php foreach($sap_het as $sp): ?>
                                            <div><strong><?=e($sp['maSP'])?></strong> - <?=e($sp['tenSP'])?>
                                                (<?=number_format($sp['soLuongTon'])?>)</div>
                                            <?php endforeach; ?>
                                            <?php if(empty($sap_het)) echo "<em>Không có</em>"; ?>
                                        </div>
                        </div>
                        <!-- Hết hàng -->
                        <div>
                            <h6><span class="badge bg-danger me-2">Hết hàng</span> <?=count($het_hang)?> sản phẩm</h6>
                            <div style="max-height:100px; overflow-y:auto; font-size:0.9rem; color:#dc3545;">
                                <?php foreach($het_hang as $sp): ?>
                                <div><strong><?=e($sp['maSP'])?></strong> - <?=e($sp['tenSP'])?> (0)</div>
                                <?php endforeach; ?>
                                <?php if(empty($het_hang)) echo "<em>Không có</em>"; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Biểu đồ nhập xuất
new Chart(document.getElementById('chartNhapXuat'), {
    type: 'bar',
    data: {
        labels: ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
        datasets: [{
                label: 'Nhập kho',
                data: <?=json_encode(array_column($nx_data,'nhap_kho'))?>,
                backgroundColor: '#0d6efd',
                borderRadius: 6
            },
            {
                label: 'Xuất kho',
                data: <?=json_encode(array_column($nx_data,'xuat_kho'))?>,
                backgroundColor: '#ffc107',
                borderRadius: 6
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Biểu đồ tròn khi mở modal
let pieChart = null;
document.getElementById('modalCanhBao').addEventListener('shown.bs.modal', function() {
    if (pieChart) pieChart.destroy();
    pieChart = new Chart(document.getElementById('chartPie'), {
        type: 'doughnut',
        data: {
            labels: ['Còn nhiều (≥50)', 'Sắp hết (<50)', 'Hết hàng'],
            datasets: [{
                data: [<?=$con?>, <?=$sap?>, <?=$het?>],
                backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                borderColor: '#fff',
                borderWidth: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php include_once('../../layout/footer.php'); ?>