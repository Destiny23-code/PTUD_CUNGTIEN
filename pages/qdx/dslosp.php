<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

// Bắt đầu session và kiểm tra đăng nhập/quyền
if (!isset($_SESSION)) session_start();

require_once("../../class/clslogin.php");
require_once("../../class/clsLapPYCKD.php"); // Include file class

$p = new login();
$pyckd = new clsLapPYCKD();

$session_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
$session_user = isset($_SESSION['user']) ? $_SESSION['user'] : '';
$session_pass = isset($_SESSION['pass']) ? $_SESSION['pass'] : '';
$session_phanquyen = isset($_SESSION['phanquyen']) ? $_SESSION['phanquyen'] : 0;

// Yêu cầu quyền Quản đốc (phanquyen = 2) để truy cập
if (!$p->confirmlogin($session_id, $session_user, $session_pass, $session_phanquyen) || $session_phanquyen != 2) {
    header("Location: ../dangnhap.php");
    exit();
}

include_once('../../layout/giaodien/qdx.php');

$msg = "";
$msgType = "danger";

// FIX: Lấy danh sách lô sản phẩm với trạng thái 'Chưa kiểm định' 
$dsLoSPChoKiemDinh = $pyckd->getLoSanPhamByTrangThai('Chưa kiểm định');
// Lấy danh sách các phiếu yêu cầu kiểm định
$dsPhieuYCKD = $pyckd->getAllPhieuYCKD();

// --- KIỂM TRA LỖI SAU KHI GỌI HÀM ---
// Lỗi này giờ đã được bắt trong clsconnect, nên phần này chỉ là dự phòng.
if (!is_array($dsLoSPChoKiemDinh)) {
    $msg = "LỖI HỆ THỐNG: Không thể tải dữ liệu Lô SP từ CSDL. Vui lòng kiểm tra file clsconnect.php và cấu trúc bảng losanpham.";
    $msgType = "danger";
    $dsLoSPChoKiemDinh = array();
}
if (!is_array($dsPhieuYCKD)) {
    $msg = "LỖI HỆ THỐNG: Không thể tải dữ liệu Phiếu YC từ CSDL. Vui lòng kiểm tra file clsconnect.php và cấu trúc bảng phieuyeucaukiemdinh.";
    $msgType = "danger";
    $dsPhieuYCKD = array();
}

// Nếu cả hai đều rỗng, nhưng không có lỗi CSDL rõ ràng, đưa ra thông báo nhẹ nhàng hơn.
if (empty($dsLoSPChoKiemDinh) && empty($dsPhieuYCKD) && empty($msg)) {
    $msg = "Dữ liệu đã được tải thành công, nhưng không có lô sản phẩm nào ở trạng thái 'Chưa kiểm định' hoặc chưa có phiếu yêu cầu nào được lập.";
    $msgType = "info";
} else {
    $msg = ""; // Xóa thông báo lỗi nếu đã có dữ liệu
}
// ------------------------------------

?>

<div class="content">
    <div class="container-fluid">
        <h3 class="mb-4 text-primary">
            <i class="bi bi-list-check me-2"></i>DANH SÁCH LÔ SẢN PHẨM & PHIẾU KIỂM ĐỊNH
        </h3>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show">
                <i class="bi bi-<?php echo $msgType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>-fill me-2"></i>
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-dark fw-bold">
                <i class="bi bi-box me-2"></i>Lô Sản Phẩm Đang Ở Trạng Thái "Chưa kiểm định" (<?php echo count($dsLoSPChoKiemDinh); ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-warning text-center">
                            <tr>
                                <th style="width: 5%;">STT</th>
                                <th style="width: 15%;">Mã Lô</th>
                                <th style="width: 25%;">Tên Lô / Tên SP</th>
                                <th style="width: 15%;">Ngày SX</th>
                                <th style="width: 15%;">Số Lượng</th>
                                <th style="width: 15%;">Trạng Thái</th>
                                <th style="width: 10%;">Lập Phiếu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dsLoSPChoKiemDinh)): ?>
                                <?php 
                                $stt_lo = 0;
                                foreach ($dsLoSPChoKiemDinh as $lo): 
                                    $stt_lo++;
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $stt_lo; ?></td>
                                    <td class="text-center fw-bold text-primary"><?php echo htmlspecialchars($lo['maLo']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($lo['tenLo']); ?></strong>
                                        <br><small class="text-muted">SP: <?php echo htmlspecialchars($lo['tenSP']); ?></small>
                                    </td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($lo['ngaySanXuat'])); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($lo['soLuong'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($lo['trangThai']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="lapkieudinh.php?maLo=<?php echo htmlspecialchars($lo['maLo']); ?>" class="btn btn-sm btn-info" title="Lập Phiếu Yêu Cầu Kiểm Định">
                                            <i class="bi bi-file-earmark-plus"></i> Lập Phiếu
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-check-circle fs-1 mb-3 d-block text-success"></i>
                                        <h5>Không có lô sản phẩm nào chưa kiểm định</h5>
                                        <p>Vui lòng kiểm tra lại quá trình sản xuất.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-info text-white fw-bold">
                <i class="bi bi-journal-text me-2"></i>Danh Sách Phiếu Yêu Cầu Kiểm Định Đã Lập (<?php echo count($dsPhieuYCKD); ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-info text-center">
                            <tr>
                                <th style="width: 5%;">STT</th>
                                <th style="width: 15%;">Mã Phiếu</th>
                                <th style="width: 15%;">Ngày Lập</th>
                                <th style="width: 25%;">Lô SP/Sản Phẩm</th>
                                <th style="width: 20%;">Người Lập</th>
                                <th style="width: 15%;">Trạng Thái</th>
                                <th style="width: 5%;">Chi Tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dsPhieuYCKD)): ?>
                                <?php 
                                $stt_phieu = 0;
                                foreach ($dsPhieuYCKD as $phieu): 
                                    $stt_phieu++;
                                    // Xác định class badge cho trạng thái
                                    $badgeClass = 'bg-secondary';
                                    if ($phieu['trangThai'] == 'Chờ kiểm định') $badgeClass = 'bg-warning text-dark';
                                    elseif ($phieu['trangThai'] == 'Đã kiểm định') $badgeClass = 'bg-success';
                                    elseif ($phieu['trangThai'] == 'Đang kiểm định') $badgeClass = 'bg-primary';
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $stt_phieu; ?></td>
                                    <td class="text-center fw-bold text-danger">PYCKD-<?php echo htmlspecialchars($phieu['maPYCKD']); ?></td>
                                    <td class="text-center"><?php echo date('d/m/Y', strtotime($phieu['ngayLap'])); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($phieu['tenLo']); ?></strong>
                                        <br><small class="text-muted">SP: <?php echo htmlspecialchars($phieu['tenSP']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($phieu['nguoiLap']); ?></td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($phieu['trangThai']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalChiTiet"
                                                data-maphieu="PYCKD-<?php echo htmlspecialchars($phieu['maPYCKD']); ?>"
                                                data-ngaylap="<?php echo date('d/m/Y', strtotime($phieu['ngayLap'])); ?>"
                                                data-nguoilap="<?php echo htmlspecialchars($phieu['nguoiLap']); ?>"
                                                data-trangthai="<?php echo htmlspecialchars($phieu['trangThai']); ?>"
                                                data-tenlo="<?php echo htmlspecialchars($phieu['tenLo']); ?>"
                                                data-tensp="<?php echo htmlspecialchars($phieu['tenSP']); ?>"
                                                data-ngaysx="<?php echo date('d/m/Y', strtotime($phieu['ngaySanXuat'])); ?>"
                                                data-ghichu="<?php echo nl2br(htmlspecialchars($phieu['ghiChu'])); ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-journal fs-1 mb-3 d-block"></i>
                                        <h5>Chưa có phiếu yêu cầu kiểm định nào được lập</h5>
                                        <p>Vui lòng lập phiếu yêu cầu kiểm định cho các lô sản phẩm.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalChiTiet" tabindex="-1" aria-labelledby="modalChiTietLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalChiTietLabel"><i class="bi bi-card-list me-2"></i>Chi Tiết Phiếu Yêu Cầu Kiểm Định</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>Mã Phiếu:</strong> <span id="detail-maphieu" class="text-danger fw-bold"></span></p>
                        <p class="mb-1"><strong>Ngày Lập:</strong> <span id="detail-ngaylap"></span></p>
                        <p class="mb-1"><strong>Người Lập:</strong> <span id="detail-nguoilap"></span></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>Trạng Thái:</strong> <span id="detail-trangthai" class="badge"></span></p>
                        <p class="mb-1"><strong>Lô Sản Phẩm:</strong> <span id="detail-tenlo" class="fw-bold"></span></p>
                        <p class="mb-1"><strong>Sản Phẩm:</strong> <span id="detail-tensp"></span></p>
                        <p class="mb-1"><strong>Ngày SX:</strong> <span id="detail-ngaysx"></span></p>
                    </div>
                    <div class="col-12">
                        <div class="card card-body bg-light">
                            <h6><i class="bi bi-pencil-square me-1"></i> Ghi Chú / Tiêu Chí Yêu Cầu:</h6>
                            <p id="detail-ghichu" class="mb-0"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
// Cập nhật dữ liệu vào Modal khi click nút Chi Tiết
document.getElementById('modalChiTiet').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget; // Button that triggered the modal
    
    // Lấy dữ liệu từ data-* attributes
    const maPhieu = button.getAttribute('data-maphieu');
    const ngayLap = button.getAttribute('data-ngaylap');
    const nguoiLap = button.getAttribute('data-nguoilap');
    const trangThai = button.getAttribute('data-trangthai');
    const tenLo = button.getAttribute('data-tenlo');
    const tenSP = button.getAttribute('data-tensp');
    const ngaySX = button.getAttribute('data-ngaysx');
    const ghiChu = button.getAttribute('data-ghichu');

    // Cập nhật nội dung Modal
    document.getElementById('detail-maphieu').textContent = maPhieu;
    document.getElementById('detail-ngaylap').textContent = ngayLap;
    document.getElementById('detail-nguoilap').textContent = nguoiLap;
    document.getElementById('detail-tenlo').textContent = tenLo;
    document.getElementById('detail-tensp').textContent = tenSP;
    document.getElementById('detail-ngaysx').textContent = ngaySX;
    document.getElementById('detail-ghichu').innerHTML = ghiChu;

    // Cập nhật trạng thái và badge class
    const detailTrangThai = document.getElementById('detail-trangthai');
    detailTrangThai.textContent = trangThai;
    detailTrangThai.className = 'badge'; // Reset class
    
    let badgeClass = 'bg-secondary';
    if (trangThai === 'Chờ kiểm định') badgeClass = 'bg-warning text-dark';
    else if (trangThai === 'Đã kiểm định') badgeClass = 'bg-success';
    else if (trangThai === 'Đang kiểm định') badgeClass = 'bg-primary';
    
    detailTrangThai.classList.add(badgeClass);
});
</script>

<?php include_once("../../layout/footer.php"); ?>