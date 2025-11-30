<?php
include_once('../../layout/giaodien/khotp.php');
require_once('../../class/clsconnect.php');

$ketnoi_instance = new ketnoi();
$conn = $ketnoi_instance->connect();

// === THỐNG KÊ TỔNG QUAN ===
$sql = "SELECT COUNT(maSP) as tong_sp, SUM(soLuongTon) as tong_ton, AVG(soLuongTon) as tb_ton FROM sanpham";
$result = $conn->query($sql);
$stats = $result->fetch_assoc();

// === DANH SÁCH SẢN PHẨM - SẮP XẾP THEO MÃ SẢN PHẨM TĂNG DẦN ===
$sql_list = "SELECT maSP, tenSP, donViTinh, soLuongTon FROM sanpham ORDER BY maSP ASC"; // ĐÃ SỬA Ở ĐÂY
$result_list = $conn->query($sql_list);
$sanpham_list = array();
while ($row = $result_list->fetch_assoc()) {
    $sanpham_list[] = $row;
}

// === NHẬP - XUẤT KHO THEO THÁNG NĂM 2025 ===
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

$result_nx = $conn->query($nhapxuat_sql);

// Tạo mảng 12 tháng
$nx_data = array();
for ($i = 1; $i <= 12; $i++) {
    $nx_data[$i] = array('nhap_kho' => 0, 'xuat_kho' => 0);
}
while ($r = $result_nx->fetch_assoc()) {
    $thang = (int)$r['thang'];
    $nx_data[$thang]['nhap_kho'] = (int)$r['nhap_kho'];
    $nx_data[$thang]['xuat_kho'] = (int)$r['xuat_kho'];
}

// === PHÂN LOẠI TỒN KHO ===
$con_nhieu = array();
$sap_het   = array();
$het_hang  = array();
$con = $sap = $het = 0;

foreach ($sanpham_list as $sp) {
    $sl = (int)$sp['soLuongTon'];
    if ($sl == 0) {
        $het++;
        $het_hang[] = $sp;
    } elseif ($sl < 50) {
        $sap++;
        $sap_het[] = $sp;
    } else {
        $con++;
        $con_nhieu[] = $sp;
    }
}
$co_canh_bao = ($het + $sap > 0);

function e($v) {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
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
    color: #0d6efd;
}

.text-ton {
    color: #0dcaf0;
}

.text-warning {
    color: #dc3545;
    font-weight: 800;
}
</style>

<div class="content">
    <div class="card shadow-sm p-4">
        <h3 class="text-primary fw-bold mb-4">Thống kê tồn kho thành phẩm</h3>

        <!-- 3 CARD THỐNG KÊ -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card" data-bs-toggle="modal" data-bs-target="#modalTonKho">
                    <div class="icon text-primary">Box</div>
                    <p class="mb-1 text-muted small">Tổng sản phẩm</p>
                    <h2 class="text-sp fw-bold"><?php echo e($stats['tong_sp']); ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" data-bs-toggle="modal" data-bs-target="#modalTonKho">
                    <div class="icon text-ton">Chart</div>
                    <p class="mb-1 text-muted small">Tổng tồn kho</p>
                    <h2 class="text-ton fw-bold"><?php echo number_format($stats['tong_ton']); ?> <small
                            class="fs-5">SP</small></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card <?php echo $co_canh_bao ? 'warning' : ''; ?>" data-bs-toggle="modal"
                    data-bs-target="#modalCanhBao">
                    <div class="icon <?php echo $co_canh_bao ? 'text-warning' : 'text-success'; ?>">Warning</div>
                    <p class="mb-1 text-muted small">Cảnh báo tồn kho</p>
                    <h2 class="text-warning fw-bold"><?php echo $het; ?> hết / <?php echo $sap; ?> sắp hết</h2>
                </div>
            </div>
        </div>

        <!-- BIỂU ĐỒ NHẬP XUẤT -->
        <div class="card shadow-sm p-4 mt-4">
            <h5 class="text-secondary mb-3">Nhập - Xuất kho theo tháng (2025)</h5>
            <canvas id="chartNhapXuat" height="400"></canvas>
        </div>
    </div>
</div>

<!-- MODAL CHI TIẾT TỒN KHO -->
<div class="modal fade" id="modalTonKho" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title">Chi tiết tồn kho</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center">
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
                                <td class="fw-bold"><?php echo e($sp['maSP']); ?></td>
                                <td class="text-start"><?php echo e($sp['tenSP']); ?></td>
                                <td><?php echo e($sp['donViTinh']); ?></td>
                                <td
                                    class="fw-bold <?php echo ($sp['soLuongTon']==0?'text-danger':($sp['soLuongTon']<50?'text-warning':'text-success')); ?>">
                                    <?php echo number_format($sp['soLuongTon']); ?>
                                    <?php if($sp['soLuongTon']==0): ?><span
                                        class="badge bg-danger ms-2">Hết</span><?php endif; ?>
                                    <?php if($sp['soLuongTon']>0 && $sp['soLuongTon']<50): ?><span
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

<!-- MODAL CẢNH BÁO TỒN KHO -->
<div class="modal fade" id="modalCanhBao" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h4 class="modal-title">Phân loại tồn kho</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <canvas id="chartPie" height="280"></canvas>
                    </div>
                    <div class="col-md-7">
                        <h5 class="text-center mb-3">Chi tiết từng nhóm</h5>
                        <div class="mb-3">
                            <h6><span class="badge bg-success me-2">Còn nhiều (≥50)</span>
                                <?php echo count($con_nhieu); ?> sản phẩm</h6>
                            <div style="max-height:140px; overflow-y:auto; font-size:0.9rem;">
                                <?php foreach($con_nhieu as $sp): ?>
                                <div>• <strong><?php echo e($sp['maSP']); ?></strong> - <?php echo e($sp['tenSP']); ?>
                                    (<?php echo number_format($sp['soLuongTon']); ?>)</div>
                                <?php endforeach; ?>
                                <?php if(empty($con_nhieu)) echo "<em>Không có</em>"; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6><span class="badge bg-warning text-dark me-2">Sắp hết (&lt;50)</span>
                                <?php echo count($sap_het); ?> sản phẩm</h6>
                            <div style="max-height:100px; overflow-y:auto; font-size:0.9rem; color:#e67e22;">
                                <?php foreach($sap_het as $sp): ?>
                                <div>• <strong><?php echo e($sp['maSP']); ?></strong> - <?php echo e($sp['tenSP']); ?>
                                    (<?php echo number_format($sp['soLuongTon']); ?>)</div>
                                <?php endforeach; ?>
                                <?php if(empty($sap_het)) echo "<em>Không có</em>"; ?>
                            </div>
                        </div>
                        <div>
                            <h6><span class="badge bg-danger me-2">Hết hàng</span> <?php echo count($het_hang); ?> sản
                                phẩm</h6>
                            <div style="max-height:100px; overflow-y:auto; font-size:0.9rem; color:#dc3545;">
                                <?php foreach($het_hang as $sp): ?>
                                <div>• <strong><?php echo e($sp['maSP']); ?></strong> - <?php echo e($sp['tenSP']); ?>
                                    (0)</div>
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
// Chuẩn bị dữ liệu nhập xuất
var nhapData = <?php echo json_encode(array_values($nx_data)); ?>;
var nhapArr = [];
var xuatArr = [];
for (var i = 0; i < nhapData.length; i++) {
    nhapArr.push(nhapData[i].nhap_kho || 0);
    xuatArr.push(nhapData[i].xuat_kho || 0);
}

// Biểu đồ cột
new Chart(document.getElementById('chartNhapXuat'), {
    type: 'bar',
    data: {
        labels: ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
        datasets: [{
                label: 'Nhập kho',
                data: nhapArr,
                backgroundColor: '#0d6efd',
                borderRadius: 6
            },
            {
                label: 'Xuất kho',
                data: xuatArr,
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

// Biểu đồ tròn
var pieChart = null;
document.getElementById('modalCanhBao').addEventListener('shown.bs.modal', function() {
    if (pieChart) pieChart.destroy();
    pieChart = new Chart(document.getElementById('chartPie'), {
        type: 'doughnut',
        data: {
            labels: ['Còn nhiều (≥50)', 'Sắp hết (<50)', 'Hết hàng'],
            datasets: [{
                data: [<?php echo $con; ?>, <?php echo $sap; ?>, <?php echo $het; ?>],
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