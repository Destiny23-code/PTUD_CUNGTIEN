<?php
session_start();
require_once('../../class/clsconnect.php');
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['hoTen'])) {
    echo json_encode(array('status'=>'error','message'=>'Chưa đăng nhập'));
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$items = isset($input['items']) ? $input['items'] : array();

if (empty($items)) {
    echo json_encode(array('status'=>'error','message'=>'Chưa chọn mục nào để xuất kho'));
    exit;
}

$ketnoi_instance = new ketnoi();
$conn = $ketnoi_instance->connect();
$conn->autocommit(false);

try {
    $ngaygio = date('d/m/Y H:i');
    $nguoi   = $_SESSION['hoTen'];
    $maNV    = isset($_SESSION['maNV']) ? (int)$_SESSION['maNV'] : 5; // mặc định nhân viên kho

    // Tạo phiếu xuất kho
    $sql = "INSERT INTO phieuxuatkho (nguoiLap, ngayLap, ngayXuat, trangThai) 
            VALUES ($maNV, CURDATE(), CURDATE(), 'Đã xuất')";
    $conn->query($sql);
    $maPXK_raw = $conn->insert_id;
    $maPXK = str_pad($maPXK_raw, 4, '0', STR_PAD_LEFT);

    $chiTiet = array();
    $tongSL  = 0;
    $donHangDaXuat = array(); // để đánh dấu hoàn thành đơn

    foreach ($items as $it) {
        $maDH = (int)$it['maDH'];
        $maSP = (int)$it['maSP'];
        $sl   = (int)$it['soLuong'];

        // Lấy thông tin khách hàng + sản phẩm
        $q = $conn->query("SELECT kh.tenKH, sp.tenSP, dh.ngayGiaoDuKien
                           FROM donhang dh
                           JOIN khachhang kh ON dh.maKH = kh.maKH
                           JOIN sanpham sp ON sp.maSP = $maSP
                           WHERE dh.maDH = $maDH LIMIT 1");
        $r = $q->fetch_assoc();

        // Ghi chi tiết xuất kho
        $conn->query("INSERT INTO chitiet_phieuxuatkho (maPXK, maDH, soLuongXuat, ghiChu) 
                      VALUES ($maPXK_raw, $maDH, $sl, NULL)");

        // Trừ tồn kho đúng sản phẩm
        $conn->query("UPDATE sanpham SET soLuongTon = soLuongTon - $sl WHERE maSP = $maSP");

        // Thu thập dữ liệu in phiếu
        $chiTiet[] = array(
            'maDH'     => $maDH,
            'tenKH'    => isset($r['tenKH']) ? $r['tenKH'] : 'Không xác định',
            'maSP'     => $maSP,
            'tenSP'    => isset($r['tenSP']) ? $r['tenSP'] : '',
            'soLuong'  => $sl,
            'ngayGiao' => isset($r['ngayGiaoDuKien']) ? date('d/m/Y', strtotime($r['ngayGiaoDuKien'])) : ''
        );
        $tongSL += $sl;
        $donHangDaXuat[$maDH] = true;
    }

    // Đánh dấu các đơn hàng đã xuất đủ → Hoàn thành
    foreach (array_keys($donHangDaXuat) as $maDH) {
        $conn->query("UPDATE donhang SET trangThai = 'Hoàn thành' WHERE maDH = $maDH");
    }

    $conn->commit();

    // Tạo HTML phiếu xuất đẹp
    $html = "<div class='text-center mb-4'>
                <h2 class='text-success fw-bold'>PHIẾU XUẤT KHO PXK$maPXK</h2>
                <p class='fs-5'>Ngày xuất: <strong>$ngaygio</strong> | Người lập: <strong>$nguoi</strong></p>
             </div>";

    $html .= "<table class='table table-bordered table-hover'>
                <thead class='table-success text-center'>
                    <tr>
                        <th>Đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Mã SP</th>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Ngày giao</th>
                    </tr>
                </thead>
                <tbody>";

    foreach ($chiTiet as $it) {
        $html .= "<tr class='text-center align-middle'>
                    <td class='fw-bold'>#" . str_pad($it['maDH'], 4, '0', STR_PAD_LEFT) . "</td>
                    <td>" . htmlspecialchars($it['tenKH']) . "</td>
                    <td>" . $it['maSP'] . "</td>
                    <td>" . htmlspecialchars($it['tenSP']) . "</td>
                    <td class='fw-bold text-primary fs-5'>" . number_format($it['soLuong']) . "</td>
                    <td>" . $it['ngayGiao'] . "</td>
                  </tr>";
    }

    $html .= "<tr class='table-info fw-bold fs-4'>
                <td colspan='4' class='text-end pe-4'>TỔNG CỘNG:</td>
                <td class='text-center text-primary'>" . number_format($tongSL) . " sp</td>
                <td></td>
              </tr></tbody></table>";

    $html .= "<div class='alert alert-success text-center mt-4 fw-bold'>
                Xuất kho thành công! Các đơn hàng đã được chuyển sang trạng thái <strong>Hoàn thành</strong>
              </div>";

    echo json_encode(array('status' => 'success', 'html' => $html));

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(array('status'=>'error','message'=>'Lỗi hệ thống: ' . $e->getMessage()));
}
?>