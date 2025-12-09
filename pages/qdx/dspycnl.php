<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION)) {
    session_start();
}

require_once("../../class/clslogin.php");

$p = new login();

$session_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
$session_user = isset($_SESSION['user']) ? $_SESSION['user'] : '';
$session_pass = isset($_SESSION['pass']) ? $_SESSION['pass'] : '';
$session_phanquyen = isset($_SESSION['phanquyen']) ? $_SESSION['phanquyen'] : 0;

if (!$p->confirmlogin($session_id, $session_user, $session_pass, $session_phanquyen) || $session_phanquyen != 2) {
    header("Location: ../dangnhap.php");
    exit();
}

error_reporting(E_ALL & ~E_NOTICE);

require_once "../../class/clsLapPYCNL.php";

$pycnl = new LapPYCNL();

// Xử lý AJAX request để lấy chi tiết phiếu
if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_detail') {
    $maPYCNL = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($maPYCNL <= 0) {
        echo json_encode(array('error' => 'Invalid ID'));
        exit();
    }
    
    $phieu = $pycnl->getPhieuYeuCauById($maPYCNL);
    if (!$phieu) {
        echo json_encode(array('error' => 'Phiếu không tồn tại'));
        exit();
    }
    
    $chiTiet = $pycnl->getChiTietPhieuYeuCau($maPYCNL);
    
    // Thêm vị trí kho cho mỗi nguyên liệu
    $viTriKho = array('Kệ A1', 'Kệ A2', 'Kệ A3', 'Kệ B1', 'Kệ B2');
    $index = 0;
    foreach ($chiTiet as &$item) {
        $item['viTriKho'] = $viTriKho[$index % count($viTriKho)];
        $index++;
    }
    
    $response = array(
        'success' => true,
        'phieu' => $phieu,
        'chiTiet' => $chiTiet
    );
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

include_once '../../layout/giaodien/qdx.php';

$allPhieu = $pycnl->getAllPhieuYeuCau();
$totalRecords = count($allPhieu);
?>

<style>
    .search-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .search-box input {
        border-radius: 20px;
        padding-left: 40px;
    }
    .search-box .bi-search {
        position: absolute;
        left: 25px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    .table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .table thead {
        background: #495057;
        color: white;
    }
    .table tbody tr {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .table tbody tr:hover {
        background-color: #e3f2fd;
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .badge-da-huy { 
        background-color: #dc3545 !important; 
        color: white !important; 
        padding: 6px 12px; 
        border-radius: 4px; 
        font-weight: 600;
        display: inline-block;
    }
    .badge-da-duyet { 
        background-color: #0d6efd !important; 
        color: white !important; 
        padding: 6px 12px; 
        border-radius: 4px; 
        font-weight: 600;
        display: inline-block;
    }
    .badge-cho-duyet { 
        background-color: #ffc107 !important; 
        color: black !important; 
        padding: 6px 12px; 
        border-radius: 4px; 
        font-weight: 600;
        display: inline-block;
    }
    .badge-da-cap { 
        background-color: #198754 !important; 
        color: white !important; 
        padding: 6px 12px; 
        border-radius: 4px; 
        font-weight: 600;
        display: inline-block;
    }
    .btn-create {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
    }
    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    /* Modal Styles - Matching Reference Design */
    .modal-xl {
        max-width: 70%;
    }
    .modal-header {
        background: #2196F3 !important;
        color: white !important;
        padding: 15px 25px;
        border-bottom: none;
    }
    .modal-title {
        font-size: 1.1rem;
        font-weight: 600;
    }
    .modal-body {
        padding: 25px;
        background: #f8f9fa;
    }
    
    /* Info Sections */
    .info-section {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        border: 1px solid #e3f2fd;
        box-shadow: 0 2px 8px rgba(33, 150, 243, 0.1);
    }
    .info-section-header {
        background: linear-gradient(135deg, #2196F3 0%, #1976d2 100%);
        padding: 12px 20px;
        margin: -20px -20px 15px -20px;
        border-radius: 12px 12px 0 0;
        font-weight: 600;
        color: white;
        box-shadow: 0 2px 4px rgba(33, 150, 243, 0.2);
    }
    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #e3f2fd;
        transition: all 0.3s ease;
    }
    .info-row:nth-child(1):hover {
        background: linear-gradient(90deg, #e3f2fd 0%, transparent 100%);
        padding-left: 8px;
        border-radius: 6px;
    }
    .info-row:nth-child(2):hover {
        background: linear-gradient(90deg, #fff3e0 0%, transparent 100%);
        padding-left: 8px;
        border-radius: 6px;
    }
    .info-row:nth-child(3):hover {
        background: linear-gradient(90deg, #f3e5f5 0%, transparent 100%);
        padding-left: 8px;
        border-radius: 6px;
    }
    .info-row:nth-child(4):hover {
        background: linear-gradient(90deg, #e8f5e9 0%, transparent 100%);
        padding-left: 8px;
        border-radius: 6px;
    }
    .info-row:nth-child(5):hover {
        background: linear-gradient(90deg, #fce4ec 0%, transparent 100%);
        padding-left: 8px;
        border-radius: 6px;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        min-width: 180px;
        flex-shrink: 0;
    }
    .info-row:nth-child(1) .info-label {
        color: #1976d2;
    }
    .info-row:nth-child(2) .info-label {
        color: #f57c00;
    }
    .info-row:nth-child(3) .info-label {
        color: #7b1fa2;
    }
    .info-row:nth-child(4) .info-label {
        color: #388e3c;
    }
    .info-row:nth-child(5) .info-label {
        color: #c2185b;
    }
    .info-label i {
        margin-right: 4px;
    }
    .info-value {
        color: #263238;
        font-weight: 600;
        flex: 1;
        text-align: left;
        padding-left: 15px;
    }
    
    /* Detail Table */
    .detail-table-wrapper {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }
    .detail-table-header {
        background: #2196F3;
        color: white;
        padding: 12px 20px;
        font-weight: 600;
        font-size: 1rem;
    }
    .detail-table {
        width: 100%;
        margin: 0;
    }
    .detail-table thead {
        background: #f5f5f5;
        border-bottom: 2px solid #2196F3;
    }
    .detail-table thead th {
        font-weight: 600;
        padding: 12px;
        color: #333;
        border-bottom: 2px solid #2196F3;
    }
    .detail-table tbody td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
    }
    .detail-table tbody tr:last-child td {
        border-bottom: none;
    }
    .detail-table tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>

<div class="content">
    <div class="container-fluid">
        <h3 class="text-primary mb-4">Danh Sách Yêu Cầu Nguyên Liệu</h3>
        
        <div class="card shadow-sm mb-3" style="background: #495057; color: white;">
            <div class="card-body py-2">
                <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Quản Lý Phiếu Yêu Cầu</h6>
            </div>
        </div>

        <div class="search-box position-relative">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm theo Mã YC, Kế hoạch...">
            <button class="btn btn-create position-absolute end-0 top-50 translate-middle-y me-3" onclick="window.location.href='pycnl.php'">
                <i class="bi bi-plus-circle me-2"></i>Tạo Yêu Cầu Mới
            </button>
        </div>

        <div class="table-container">
            <table class="table table-hover mb-0" id="phieuTable">
                <thead>
                    <tr>
                        <th>Mã Phiếu Yêu Cầu</th>
                        <th>Kế Hoạch Sản Xuất</th>
                        <th>Người Lập</th>
                        <th>Ngày Lập</th>
                        <th>Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (!empty($allPhieu)) {
                        foreach ($allPhieu as $phieu) {
                            $trangThai = $phieu['trangThai'];
                            $badge_class = 'badge-cho-duyet';
                            if ($trangThai == 'Đã duyệt') $badge_class = 'badge-da-duyet';
                            elseif ($trangThai == 'Đã cấp') $badge_class = 'badge-da-cap';
                            elseif ($trangThai == 'Đã hủy') $badge_class = 'badge-da-huy';
                            
                            $tenNguoiLap = !empty($phieu['tenNguoiLap']) ? $phieu['tenNguoiLap'] : (!empty($phieu['nguoiLap']) ? $phieu['nguoiLap'] : 'N/A');
                            $tenXuong = !empty($phieu['tenXuong']) ? $phieu['tenXuong'] : 'N/A';
                    ?>
                    <tr onclick="xemChiTiet(<?php echo $phieu['maPYCNL']; ?>)">
                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($phieu['maPhieu']); ?></td>
                        <td>
                            <?php 
                            if (!empty($phieu['tenSP'])) {
                                echo 'Kế hoạch ' . htmlspecialchars($phieu['tenSP']) . ' (KH' . htmlspecialchars($phieu['maKHSX']) . ')';
                            } else {
                                echo 'Kế hoạch sản xuất (KH' . htmlspecialchars($phieu['maKHSX']) . ')';
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($tenNguoiLap); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($phieu['ngayLap'])); ?></td>
                        <td><span class="<?php echo $badge_class; ?>"><?php echo htmlspecialchars($trangThai); ?></span></td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Chưa có phiếu yêu cầu nguyên liệu nào.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Chi Tiết Phiếu -->
<div class="modal fade" id="modalChiTiet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%); color: white;">
                <h5 class="modal-title" id="modalTitle">
                    <i class="bi bi-file-text me-2"></i>Chi Tiết Phiếu Yêu Cầu Nguyên Liệu
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="modalFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function xemChiTiet(id) {
    // Hiển thị modal
    var modal = new bootstrap.Modal(document.getElementById('modalChiTiet'));
    modal.show();
    
    // Reset nội dung
    $('#modalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div></div>');
    $('#modalFooter').html('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i>Đóng</button>');
    
    // Lấy dữ liệu qua AJAX
    $.ajax({
        url: 'dspycnl.php?ajax=get_detail&id=' + id,
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                renderChiTiet(data.phieu, data.chiTiet);
            } else {
                $('#modalBody').html('<div class="alert alert-danger">Lỗi: ' + (data.error || 'Không thể tải dữ liệu') + '</div>');
            }
        },
        error: function() {
            $('#modalBody').html('<div class="alert alert-danger">Lỗi kết nối! Vui lòng thử lại.</div>');
        }
    });
}

function renderChiTiet(phieu, chiTiet) {
    var trangThai = phieu.trangThai;
    var badgeClass = 'badge-cho-duyet';
    if (trangThai == 'Đã duyệt') badgeClass = 'badge-da-duyet';
    else if (trangThai == 'Đã cấp') badgeClass = 'badge-da-cap';
    else if (trangThai == 'Đã hủy') badgeClass = 'badge-da-huy';
    
    var nguoiLap = phieu.tenNguoiLap || phieu.nguoiLap || 'N/A';
    var tenSP = phieu.tenSP || '';
    var tenXuong = phieu.tenXuong || 'Chưa xác định';
    var ghiChu = phieu.ghiChu || 'Không có ghi chú';
    
    // Cập nhật tiêu đề
    $('#modalTitle').html('<i class="bi bi-file-text me-2"></i>Chi Tiết Phiếu Yêu Cầu Nguyên Liệu: ' + phieu.maPhieu);
    
    // Render nội dung - Improved Layout
    var html = '<div class="row g-3">';
    
    // Thông Tin Chung
    html += '<div class="col-md-6">';
    html += '<div class="info-section">';
    html += '<div class="info-section-header"><i class="bi bi-info-circle me-2"></i>Thông Tin Chung</div>';
    html += '<div class="info-row">';
    html += '<span class="info-label"><i class="bi bi-calendar-check me-1"></i>Mã Kế Hoạch SX:</span>';
    html += '<span class="info-value"><strong>';
    if (tenSP) {
        html += tenSP + ' <span class="badge bg-primary">KH' + phieu.maKHSX + '</span>';
    } else {
        html += '<span class="badge bg-primary">KH' + phieu.maKHSX + '</span>';
    }
    html += '</strong></span></div>';
    
    html += '<div class="info-row">';
    html += '<span class="info-label"><i class="bi bi-building me-1"></i>Xưởng:</span>';
    html += '<span class="info-value">' + tenXuong + '</span>';
    html += '</div>';
    
    html += '<div class="info-row">';
    html += '<span class="info-label"><i class="bi bi-person me-1"></i>Người Lập:</span>';
    html += '<span class="info-value">' + nguoiLap + '</span>';
    html += '</div>';
    
    html += '<div class="info-row">';
    html += '<span class="info-label"><i class="bi bi-calendar3 me-1"></i>Ngày Lập:</span>';
    html += '<span class="info-value">' + phieu.ngayLap + '</span>';
    html += '</div>';
    
    html += '<div class="info-row">';
    html += '<span class="info-label"><i class="bi bi-flag me-1"></i>Trạng Thái:</span>';
    html += '<span class="info-value"><span class="' + badgeClass + '">' + trangThai + '</span></span>';
    html += '</div>';
    html += '</div></div>';
    
    // Ghi Chú
    html += '<div class="col-md-6">';
    html += '<div class="info-section" style="height: 100%;">';
    html += '<div class="info-section-header"><i class="bi bi-clipboard-check me-2"></i>Ghi Chú Yêu Cầu</div>';
    html += '<div class="p-3" style="background: #f8f9fa; border-radius: 6px; min-height: 150px;">';
    html += '<p class="mb-0" style="white-space: pre-wrap; line-height: 1.6;">' + (ghiChu || '<em class="text-muted">Không có ghi chú</em>') + '</p>';
    html += '</div>';
    html += '</div></div>';
    html += '</div>';
    
    // Bảng chi tiết nguyên liệu - Improved Design
    html += '<div class="detail-table-wrapper mt-4">';
    html += '<div class="detail-table-header"><i class="bi bi-list-check me-2"></i>Chi Tiết Nguyên Liệu Yêu Cầu</div>';
    html += '<table class="table detail-table mb-0">';
    html += '<thead>';
    html += '<tr>';
    html += '<th style="width: 8%;" class="text-center">Mã NL</th>';
    html += '<th style="width: 35%;">Tên Nguyên Liệu</th>';
    html += '<th style="width: 10%;" class="text-center">Đơn Vị</th>';
    html += '<th style="width: 15%;" class="text-end">Định Mức Cần</th>';
    html += '<th style="width: 17%;" class="text-end">Số Lượng Yêu Cầu</th>';
    html += '<th style="width: 15%;" class="text-center">Vị Trí Kho</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    
    if (chiTiet && chiTiet.length > 0) {
        for (var i = 0; i < chiTiet.length; i++) {
            var item = chiTiet[i];
            var soLuong = parseFloat(item.soLuongYeuCau);
            html += '<tr>';
            html += '<td class="text-center"><span class="badge bg-secondary">' + item.maNL + '</span></td>';
            html += '<td><strong>' + item.tenNL + '</strong></td>';
            html += '<td class="text-center"><span class="badge bg-light text-dark">' + item.donViTinh + '</span></td>';
            html += '<td class="text-end"><span style="font-family: monospace; font-size: 0.95rem;">' + formatNumberWithComma(soLuong) + '</span></td>';
            html += '<td class="text-end"><strong style="font-family: monospace; font-size: 1rem; color: #2196F3;">' + formatNumberWithComma(soLuong) + '</strong></td>';
            html += '<td class="text-center"><span class="badge bg-info">' + item.viTriKho + '</span></td>';
            html += '</tr>';
        }
    } else {
        html += '<tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Không có chi tiết nguyên liệu.</td></tr>';
    }
    
    html += '</tbody></table></div>';
    
    $('#modalBody').html(html);
    
    // Render footer buttons
    // Quản đốc xưởng (phanquyen = 2) CHỈ được xem, KHÔNG được duyệt/hủy/cấp
    var footerHtml = '';
    var phanQuyen = <?php echo $session_phanquyen; ?>;
    
    // Chỉ hiển thị nút thao tác nếu KHÔNG phải quản đốc xưởng
    if (phanQuyen != 2) {
        if (trangThai == 'Chờ duyệt') {
            footerHtml += '<button class="btn btn-danger" onclick="xuLyPhieu(' + phieu.maPYCNL + ', \'cancel\')"><i class="bi bi-x-circle me-2"></i>Hủy Phiếu</button>';
            footerHtml += '<button class="btn btn-success" onclick="xuLyPhieu(' + phieu.maPYCNL + ', \'approve\')"><i class="bi bi-check-circle me-2"></i>Duyệt Phiếu</button>';
        } else if (trangThai == 'Đã duyệt') {
            footerHtml += '<button class="btn btn-primary" onclick="xuLyPhieu(' + phieu.maPYCNL + ', \'supply\')"><i class="bi bi-box-seam me-2"></i>Đã Cấp Nguyên Liệu</button>';
        }
    }
    
    footerHtml += '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-2"></i>Đóng</button>';
    
    $('#modalFooter').html(footerHtml);
}

function formatNumber(num) {
    return num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function formatNumberWithComma(num) {
    // Format số với dấu phẩy thập phân và dấu chấm phân cách hàng nghìn
    var parts = num.toFixed(2).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    return parts.join(',');
}

function xuLyPhieu(id, action) {
    var message = '';
    if (action == 'cancel') message = 'Bạn có chắc muốn hủy phiếu này?';
    else if (action == 'approve') message = 'Bạn có chắc muốn duyệt phiếu này?';
    else if (action == 'supply') message = 'Xác nhận đã cấp nguyên liệu cho phiếu này?';
    
    if (confirm(message)) {
        window.location.href = 'xuly_pycnl.php?action=' + action + '&id=' + id;
    }
}

// Tìm kiếm với hiệu ứng
$('#searchInput').on('keyup', function() {
    var value = $(this).val().toLowerCase();
    $('#phieuTable tbody tr').each(function() {
        var row = $(this);
        var text = row.text().toLowerCase();
        if (text.indexOf(value) > -1) {
            row.fadeIn(200);
        } else {
            row.fadeOut(200);
        }
    });
});

// Highlight khi hover
$('#phieuTable tbody tr').hover(
    function() {
        $(this).css('cursor', 'pointer');
    }
);
</script>

<?php include_once "../../layout/footer.php"; ?>
