<?php
session_start();
require_once('../../class/clsconnect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['hoTen'])) {
    echo json_encode(['status'=>'error','message'=>'Chưa đăng nhập']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$items = $input['items'] ?? [];

if (empty($items)) {
    echo json_encode(['status'=>'error','message'=>'Chưa chọn đơn hàng']);
    exit;
}

// SỬA: Dùng cách gọi ổn định – không cần sửa clsconnect.php
$ketnoi_instance = new ketnoi();
$conn = $ketnoi_instance->connect();

$conn->autocommit(false);

try {
    $ngaygio = date('d/m/Y H:i');
    $nguoi   = $_SESSION['hoTen'];
    $maNV    = $_SESSION['maNV'] ?? 1;

    // Tạo phiếu xuất kho
    $conn->query("INSERT INTO phieuxuatkho (nguoiLap, ngayLap, ngayXuat, trangThai) 
                  VALUES ($maNV, CURDATE(), CURDATE(), 'Đã xuất')");
    $maPXK = str_pad($conn->insert_id, 4, '0', STR_PAD_LEFT);

    $chiTiet = [];
    $tongSL  = 0;

    foreach ($items as $it) {
    $maDH = (int)$it['maDH'];
    $sl   = (int)$it['soLuong'];

    // SỬA ĐOẠN NÀY – JOIN ĐÚNG BẢNG
    $q = $conn->query("SELECT 
            kh.tenKH,
            sp.maSP,
            sp.tenSP,
            dh.ngayGiaoDuKien
        FROM chitiet_donhang ct
        JOIN donhang dh ON ct.maDH = dh.maDH
        JOIN khachhang kh ON dh.maKH = kh.maKH
        JOIN sanpham sp ON ct.maSP = sp.maSP
        WHERE ct.maDH = $maDH 
        LIMIT 1");
    $r = $q->fetch_assoc();

        // Ghi chi tiết phiếu xuất
        $conn->query("INSERT INTO chitiet_phieuxuatkho (maPXK, maDH, soLuongXuat) 
                      VALUES ('$maPXK', $maDH, $sl)");

        // Trừ tồn kho
        $conn->query("UPDATE sanpham sp 
                      JOIN chitiet_donhang ct ON sp.maSP = ct.maSP 
                      SET sp.soLuongTon = sp.soLuongTon - $sl 
                      WHERE ct.maDH = $maDH");

        $chiTiet[] = [
            'maDH'     => $maDH,
            'tenKH'    => $r['tenKH'] ?? 'Không xác định',
            'maSP'     => $r['maSP'] ?? '',
            'tenSP'    => $r['tenSP'] ?? '',
            'soLuong'  => $sl,
            'ngayGiao' => $r['ngayGiaoDuKien'] ? date('d/m/Y', strtotime($r['ngayGiaoDuKien'])) : ''
        ];
        $tongSL += $sl;
    }

    // Đánh dấu đơn hàng hoàn thành
    foreach ($items as $it) {
        $conn->query("UPDATE donhang SET trangThai = 'Hoàn thành' WHERE maDH = " . (int)$it['maDH']);
    }

    $conn->commit();

    // Tạo phiếu xuất đẹp
    $html = "<div class='text-center mb-4'>
                <h2 class='text-success fw-bold'>PHIẾU XUẤT KHO PXK$maPXK</h2>
                <p class='fs-5'>Ngày xuất: <strong>$ngaygio</strong> | Người xuất: <strong>$nguoi</strong></p>
             </div>";

    $html .= "<table class='table table-bordered table-hover'>
                <thead class='table-success text-center'>
                    <tr>
                        <th>ĐH</th>
                        <th>Tên khách hàng</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Ngày giao</th>
                    </tr>
                </thead>
                <tbody>";

    foreach ($chiTiet as $it) {
        $html .= "<tr class='text-center align-middle'>
                    <td class='fw-bold'>#" . str_pad($it['maDH'], 4, '0', STR_PAD_LEFT) . "</td>
                    <td>{$it['tenKH']}</td>
                    <td>{$it['maSP']}</td>
                    <td>{$it['tenSP']}</td>
                    <td class='fw-bold text-primary fs-5'>" . number_format($it['soLuong']) . "</td>
                    <td>{$it['ngayGiao']}</td>
                  </tr>";
    }

    $html .= "<tr class='table-info fw-bold fs-4'>
                <td colspan='4' class='text-end pe-4'>TỔNG CỘNG:</td>
                <td class='text-center text-primary'>" . number_format($tongSL) . " sp</td>
                <td></td>
              </tr>
              </tbody></table>";

    $html .= "<div class='alert alert-success text-center mt-4 fw-bold'>
                Xuất kho thành công! Đơn hàng đã chuyển trạng thái <strong>Hoàn thành</strong>
              </div>";

    echo json_encode(['status' => 'success', 'html' => $html]);

} catch(Exception $e) {
    $conn->rollback();
    echo json_encode(['status'=>'error','message'=>'Lỗi: ' . $e->getMessage()]);
}
?>