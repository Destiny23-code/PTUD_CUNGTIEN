<?php
include_once('../../layout/giaodien/khotp.php');
require_once('../../class/clsconnect.php');

$ketnoi_instance = new ketnoi();
$conn = $ketnoi_instance->connect();

// Truy vấn đã có ORDER BY maPKD DESC → mới nhất lên đầu
$sql = "SELECT p.maPKD, p.ngayLap, p.nguoiLap, p.tieuChi, p.maLo, p.maPhieu, p.ketQuaBaoCao, 
               l.maSP, l.ngaySX, l.soLuong AS soLuongLo, sp.tenSP
        FROM phieubaocaochatluong p 
        LEFT JOIN losanpham l ON p.maLo = l.maLo
        LEFT JOIN sanpham sp ON l.maSP = sp.maSP
        ORDER BY p.maPKD DESC";  // Đảm bảo sắp xếp giảm dần theo mã báo cáo

$res = $conn->query($sql);
$rows = array();

if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        
        // Xử lý kết quả báo cáo
        $ketqua = isset($r['ketQuaBaoCao']) ? trim($r['ketQuaBaoCao']) : '';
        if ($ketqua == '' || $ketqua === null) {
            $ketqua = 'Chưa có';
        } elseif (stripos($ketqua, 'Đạt') !== false) {
            $ketqua = 'Đạt';
        } elseif (stripos($ketqua, 'Không đạt') !== false) {
            $ketqua = 'Không đạt';
        } else {
            $ketqua = 'Khác';
        }
        $r['ketQuaBaoCao'] = $ketqua;

        // Định dạng số lượng lô
        if (isset($r['soLuongLo']) && is_numeric($r['soLuongLo']) && $r['soLuongLo'] > 0) {
            $r['soLuongLo_formatted'] = number_format($r['soLuongLo']) . ' SP';
        } else {
            $r['soLuongLo_formatted'] = 'Chưa xác định';
        }
        
        $rows[] = $r;
    }
}

// Hàm escape HTML
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<div class="content">
    <div class="card shadow-sm p-4">
        <h5 class="fw-bold text-primary mb-3">
            Kết quả báo cáo chất lượng
        </h5>

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center align-middle">
                <thead class="table-primary">
                    <tr>
                        <th width="8%">Mã báo cáo</th>
                        <th width="8%">Mã SP</th>
                        <th width="20%">Tên sản phẩm</th>
                        <th width="8%">Mã lô</th>
                        <th width="10%">Số lượng</th>
                        <th width="15%">Người lập</th>
                        <th width="12%">Ngày lập</th>
                        <th width="10%">Kết quả</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Chưa có dữ liệu báo cáo chất lượng.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach($rows as $r): ?>
                    <tr style="cursor:pointer;" onclick='moChiTiet(<?php echo json_encode($r); ?>)'>
                        <td><strong><?php echo e($r['maPKD']); ?></strong></td>
                        <td><?php echo e($r['maSP']); ?></td>
                        <td class="text-start"><?php echo e($r['tenSP']); ?></td>
                        <td><?php echo e($r['maLo']); ?></td>
                        <td class="text-primary fw-bold"><?php echo $r['soLuongLo_formatted']; ?></td>
                        <td><?php echo e($r['nguoiLap']); ?></td>
                        <td>
                            <?php 
                                if (!empty($r['ngayLap']) && $r['ngayLap'] != '0000-00-00') {
                                    echo date('d/m/Y', strtotime($r['ngayLap']));
                                } else {
                                    echo '-';
                                }
                                ?>
                        </td>
                        <td>
                            <span
                                class="badge <?php echo ($r['ketQuaBaoCao'] == 'Đạt') ? 'bg-success' : 'bg-danger'; ?> px-3 py-2">
                                <?php echo e($r['ketQuaBaoCao']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal chi tiết -->
<div class="modal fade" id="modalChiTiet" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Chi tiết báo cáo chất lượng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Mã báo cáo</th>
                        <td id="ctMaBaoCao"></td>
                    </tr>
                    <tr>
                        <th>Mã sản phẩm</th>
                        <td id="ctMaSP"></td>
                    </tr>
                    <tr>
                        <th>Tên sản phẩm</th>
                        <td id="ctTenSP"></td>
                    </tr>
                    <tr>
                        <th>Mã lô</th>
                        <td id="ctMaLo"></td>
                    </tr>
                    <tr>
                        <th>Số lượng lô</th>
                        <td id="ctSoLuongLo"></td>
                    </tr>
                    <tr>
                        <th>Người lập</th>
                        <td id="ctNguoiLap"></td>
                    </tr>
                    <tr>
                        <th>Ngày lập</th>
                        <td id="ctNgayLap"></td>
                    </tr>
                    <tr>
                        <th>Kết quả kiểm định</th>
                        <td id="ctKetQua"></td>
                    </tr>
                </table>
                <div class="mt-4">
                    <strong class="text-primary">Tiêu chí kiểm định:</strong>
                    <div id="ctTieuChi" class="border p-3 mt-2 bg-light"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">
var duLieuBaoCao = <?php echo json_encode($rows); ?>;

function moChiTiet(bc) {
    document.getElementById("ctMaBaoCao").textContent = bc.maPKD || '';
    document.getElementById("ctMaSP").textContent = bc.maSP || '';
    document.getElementById("ctTenSP").textContent = bc.tenSP || '';
    document.getElementById("ctMaLo").textContent = bc.maLo || '';
    document.getElementById("ctSoLuongLo").textContent = bc.soLuongLo_formatted || 'Chưa xác định';
    document.getElementById("ctNguoiLap").textContent = bc.nguoiLap || '';

    // Định dạng ngày
    var ngayLap = bc.ngayLap ? bc.ngayLap : '';
    if (ngayLap && ngayLap !== '0000-00-00') {
        var d = new Date(ngayLap);
        ngayLap = ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth() + 1)).slice(-2) + '/' + d.getFullYear();
    } else {
        ngayLap = '-';
    }
    document.getElementById("ctNgayLap").textContent = ngayLap;

    // Kết quả
    var badgeClass = (bc.ketQuaBaoCao === 'Đạt') ? 'bg-success' : 'bg-danger';
    document.getElementById("ctKetQua").innerHTML =
        '<span class="badge ' + badgeClass + ' px-3 py-2 fs-6">' + (bc.ketQuaBaoCao || 'Chưa có') + '</span>';

    // Tiêu chí kiểm định
    var tieuChi = bc.tieuChi || '';
    var lines = tieuChi.split(/[\r\n,;]+/);
    var html = '';
    for (var i = 0; i < lines.length; i++) {
        var line = lines[i].replace(/^\s+|\s+$/g, '');
        if (line !== '') {
            html += '<li class="mb-1">' + line + '</li>';
        }
    }
    document.getElementById("ctTieuChi").innerHTML = (html !== '') ?
        '<ul class="mb-0">' + html + '</ul>' :
        '<em class="text-muted">Không có tiêu chí kiểm định</em>';

    var modal = new bootstrap.Modal(document.getElementById('modalChiTiet'));
    modal.show();
}
</script>

<?php include_once('../../layout/footer.php'); ?>