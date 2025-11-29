<?php
require_once('../../class/session_init.php');
require_once('../../class/clsconnect.php');
include_once('../../layout/giaodien/khotp.php');

$conn = (new ketnoi())->connect();

// CHỈ HIỆN BÁO CÁO ĐÃ CÓ KẾT QUẢ (Đạt hoặc Không đạt) - ĐÚNG CHUẨN THỰC TẾ
$sql = "SELECT p.maPKD, p.ngayLap, p.nguoiLap, p.tieuChi, p.maLo, p.maPhieu, p.ketQuaBaoCao, 
               l.maSP, l.ngaySX, l.soLuong as soLuongLo, sp.tenSP, sp.soLuongTon, sp.hanSuDung
        FROM phieubaocaochatluong p 
        LEFT JOIN losanpham l ON p.maLo = l.maLo
        LEFT JOIN sanpham sp ON l.maSP = sp.maSP
        WHERE p.ketQuaBaoCao IS NOT NULL 
          AND p.ketQuaBaoCao != '' 
          AND p.ketQuaBaoCao IN ('Đạt', 'Không đạt')
        ORDER BY CAST(p.maPKD AS UNSIGNED) DESC";  // mới nhất lên đầu
$res = $conn->query($sql);
$rows = [];
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $r['hanSuDung_formatted'] = ($r['hanSuDung'] && $r['hanSuDung'] != '0000-00-00') 
            ? date('d/m/Y', strtotime($r['hanSuDung'])) : 'Không xác định';
        $r['soLuongLo_formatted'] = $r['soLuongLo'] ? number_format($r['soLuongLo']) . ' SP' : 'Chưa xác định';
        $rows[] = $r;
    }
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>

<style>
.table-hover tbody tr:hover {
    background-color: #f0f8ff;
    cursor: pointer;
}

.tieu-chi {
    margin-top: 20px;
}

.tieu-chi ul {
    margin: 0;
    padding-left: 20px;
}

.tieu-chi li {
    margin-bottom: 8px;
}

.badge {
    padding: 0.4rem 0.8rem;
    font-size: 0.9rem;
}

#ctSoLuongLo {
    font-weight: bold;
    color: #0d6efd;
}
</style>

<div class="content">
    <div class="card shadow-sm p-4">
        <h5 class="fw-bold text-primary mb-3">
            <i class="bi bi-file-earmark-check me-2"></i>Kết quả báo cáo chất lượng
        </h5>

        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" id="timMaBaoCao" class="form-control" placeholder="Tìm theo mã báo cáo...">
            </div>
            <div class="col-md-3">
                <input type="date" id="timNgayLap" class="form-control">
            </div>
            <div class="col-md-2">
                <select id="timTrangThai" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="Đạt">Đạt</option>
                    <option value="Không đạt">Không đạt</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" onclick="locBaoCao()">
                    <i class="bi bi-search"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary w-100" onclick="hienThiBaoCao()">Đặt lại</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle" id="baoCaoTable">
                <thead class="table-primary text-center">
                    <tr>
                        <th>Mã báo cáo</th>
                        <th>Mã SP</th>
                        <th>Tên SP</th>
                        <th>Mã lô</th>
                        <th>Số lượng</th>
                        <th>Người lập</th>
                        <th>Ngày lập</th>
                        <th>Kết quả</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            <i class="bi bi-info-circle"></i> Chưa có báo cáo chất lượng nào được hoàn tất.
                        </td>
                    </tr>
                    <?php else: foreach($rows as $r): ?>
                    <tr class="baocao-row" onclick='moChiTiet(<?= json_encode($r) ?>)' style="cursor:pointer;">
                        <td><?= e($r['maPKD']) ?></td>
                        <td><?= e($r['maSP']) ?></td>
                        <td><?= e($r['tenSP']) ?></td>
                        <td><?= e($r['maLo']) ?></td>
                        <td class="text-center fw-bold text-primary">
                            <?= $r['soLuongLo_formatted'] ?>
                        </td>
                        <td><?= e($r['nguoiLap']) ?></td>
                        <td><?= e($r['ngayLap']) ?></td>
                        <td class="text-center">
                            <span class="badge <?= $r['ketQuaBaoCao']==='Đạt' ? 'bg-success' : 'bg-danger' ?>">
                                <?= e($r['ketQuaBaoCao']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal chi tiết (giữ nguyên đẹp) -->
<div class="modal fade" id="modalChiTiet" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-check"></i> Chi tiết báo cáo chất lượng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered mb-4">
                    <tr>
                        <th width="30%">Mã báo cáo</th>
                        <td id="ctMaBaoCao"></td>
                    </tr>
                    <tr>
                        <th>Mã SP</th>
                        <td id="ctMaSP"></td>
                    </tr>
                    <tr>
                        <th>Tên SP</th>
                        <td id="ctTenSP"></td>
                    </tr>
                    <tr>
                        <th>Mã lô</th>
                        <td id="ctMaLo"></td>
                    </tr>
                    <tr>
                        <th>Số lượng lô</th>
                        <td id="ctSoLuongLo" class="fw-bold text-primary"></td>
                    </tr>
                    <tr>
                        <th>Hạn sử dụng</th>
                        <td id="ctHanSuDung" class="text-danger fw-bold"></td>
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
                        <th>Kết quả</th>
                        <td><span id="ctTrangThai" class="badge"></span></td>
                    </tr>
                </table>

                <div class="tieu-chi">
                    <h6 class="fw-bold text-primary mb-2"><i class="bi bi-list-check"></i> Tiêu chí kiểm định:</h6>
                    <div id="ctTieuChi"
                        style="background:#f8f9fa;padding:15px;border-radius:8px;max-height:250px;overflow-y:auto;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const duLieuBaoCao = <?= json_encode($rows) ?>;

function hienThiBaoCao(ds = duLieuBaoCao) {
    const tbody = document.querySelector("#baoCaoTable tbody");
    tbody.innerHTML = ds.length === 0 ?
        '<tr><td colspan="8" class="text-center text-muted"><i class="bi bi-info-circle"></i> Chưa có báo cáo chất lượng nào được hoàn tất.</td></tr>' :
        '';
    ds.forEach(bc => {
        const badge = bc.ketQuaBaoCao === 'Đạt' ? 'bg-success' : 'bg-danger';
        tbody.innerHTML += `<tr class="baocao-row" onclick='moChiTiet(${JSON.stringify(bc)})'>
            <td>${bc.maPKD||''}</td>
            <td>${bc.maSP||''}</td>
            <td>${bc.tenSP||''}</td>
            <td>${bc.maLo||''}</td>
            <td class="text-center fw-bold text-primary">${bc.soLuongLo_formatted||'Chưa xác định'}</td>
            <td>${bc.nguoiLap||''}</td>
            <td>${bc.ngayLap||''}</td>
            <td class="text-center"><span class="badge ${badge}">${bc.ketQuaBaoCao||''}</span></td>
        </tr>`;
    });
}

function locBaoCao() {
    const ma = document.getElementById("timMaBaoCao").value.trim().toLowerCase();
    const ngay = document.getElementById("timNgayLap").value;
    const tt = document.getElementById("timTrangThai").value;

    const filtered = duLieuBaoCao.filter(bc =>
        (!ma || String(bc.maPKD || '').toLowerCase().includes(ma)) &&
        (!ngay || bc.ngayLap === ngay) &&
        (!tt || (bc.ketQuaBaoCao || '') === tt)
    );
    hienThiBaoCao(filtered);
}

function moChiTiet(bc) {
    document.getElementById("ctMaBaoCao").textContent = bc.maPKD || '';
    document.getElementById("ctMaSP").textContent = bc.maSP || '';
    document.getElementById("ctTenSP").textContent = bc.tenSP || '';
    document.getElementById("ctMaLo").textContent = bc.maLo || '';
    document.getElementById("ctNguoiLap").textContent = bc.nguoiLap || '';
    document.getElementById("ctNgayLap").textContent = bc.ngayLap || '';
    document.getElementById("ctSoLuongLo").textContent = bc.soLuongLo_formatted || 'Chưa xác định';
    document.getElementById("ctHanSuDung").textContent = bc.hanSuDung_formatted || 'Không xác định';

    const badge = document.getElementById("ctTrangThai");
    badge.textContent = bc.ketQuaBaoCao || '';
    badge.className = 'badge ' + (bc.ketQuaBaoCao === 'Đạt' ? 'bg-success' : 'bg-danger');

    const tieuChi = bc.tieuChi || 'Không có thông tin tiêu chí.';
    const lines = tieuChi.split(/[,;]/).map(s => s.trim()).filter(s => s);
    document.getElementById("ctTieuChi").innerHTML = lines.length > 1 ?
        '<ul>' + lines.map(l => `<li>${l}</li>`).join('') + '</ul>' : tieuChi;

    new bootstrap.Modal(document.getElementById("modalChiTiet")).show();
}

hienThiBaoCao();
document.getElementById("timMaBaoCao").addEventListener("keypress", e => e.key === "Enter" && locBaoCao());
</script>

<?php include_once('../../layout/footer.php'); ?>