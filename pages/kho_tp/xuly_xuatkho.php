<?php
require_once('../../class/session_init.php');
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

$conn = (new ketnoi())->connect();
$conn->autocommit(false);

try {
    $ngaygio = date('d/m/Y H:i');
    $nguoi = $_SESSION['hoTen'];

    $conn->query("INSERT INTO phieuxuatkho (nguoiLap, ngayLap, ngayXuat, trangThai) VALUES (1, CURDATE(), CURDATE(), 'Đã xuất')");
    $maPXK = str_pad($conn->insert_id, 4, '0', STR_PAD_LEFT);

    $chiTiet = [];
    $tongSL = 0;

    foreach ($items as $it) {
        $maDH = $it['maDH'];
        $maSP = $it['maSP'];
        $sl   = $it['soLuong'];

        // ĐÃ XÓA phần lấy lô sản phẩm ở đây
        $q = $conn->query("SELECT kh.tenKH, sp.tenSP, sp.loaiSP, dh.ngayGiaoDuKien
                           FROM chitiet_donhang ct
                           JOIN khachhang kh ON ct.maKH=kh.maKH 
                           JOIN sanpham sp ON ct.maSP=sp.maSP
                           JOIN donhang dh ON ct.maDH=dh.maDH
                           WHERE ct.maDH=$maDH AND ct.maSP=$maSP
                           LIMIT 1");
        $r = $q->fetch_assoc();

        $conn->query("INSERT INTO chitiet_phieuxuatkho 
                     (maPXK, maDH, maSP, soLuongXuat, tenKH, tenSP) 
                     VALUES ('$maPXK', $maDH, $maSP, $sl, 
                             '{$conn->real_escape_string($r['tenKH'])}', 
                             '{$conn->real_escape_string($r['tenSP'])}')");

        $conn->query("UPDATE sanpham SET soLuongTon = soLuongTon - $sl WHERE maSP = $maSP");

        $chiTiet[] = [
            'maDH' => $maDH,
            'tenKH' => $r['tenKH'],
            // ĐÃ XÓA 'maLo' ở đây
            'tenSP' => $r['tenSP'],
            'loaiSP' => $r['loaiSP'] ?: 'Nước suối',
            'soLuong' => $sl,
            'ngayGiao' => $r['ngayGiaoDuKien'] ? date('d/m/Y', strtotime($r['ngayGiaoDuKien'])) : ''
        ];
        $tongSL += $sl;
    }

    foreach ($items as $it) {
        $conn->query("UPDATE donhang SET trangThai='Hoàn thành' WHERE maDH={$it['maDH']}");
    }

    $conn->commit();

    $html = "<p class='fw-bold fs-4 text-success'>PHIẾU XUẤT KHO PXK$maPXK</p>";
    $html .= "<p><strong>Ngày xuất:</strong> $ngaygio &nbsp;&nbsp; <strong>Người xuất kho:</strong> $nguoi</p>";
    $html .= "<table class='table table-bordered mt-3'>
                <thead class='table-secondary'>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Tên sản phẩm</th>   <!-- ĐÃ XÓA CỘT LÔ -->
                        <th>Loại</th>
                        <th>Số lượng</th>
                        <th>Ngày giao hàng</th>
                    </tr>
                </thead>
                <tbody>";

    foreach ($chiTiet as $it) {
        $html .= "<tr>
                    <td>#" . str_pad($it['maDH'], 4, '0', STR_PAD_LEFT) . "</td>
                    <td>{$it['tenKH']}</td>
                    <td>{$it['tenSP']}</td>   <!-- ĐÃ XÓA CỘT LÔ -->
                    <td>{$it['loaiSP']}</td>
                    <td class='text-end fw-bold'>" . number_format($it['soLuong']) . "</td>
                    <td class='text-center'>{$it['ngayGiao']}</td>
                  </tr>";
    }

    $html .= "</tbody></table>";
    $html .= "<div class='text-end fw-bold fs-5 text-success mt-3'>
                Tổng cộng: " . number_format($tongSL) . " sản phẩm
              </div>";
    $html .= "<div class='alert alert-success mt-3 text-center'>
                Các đơn hàng đã được chuyển sang trạng thái <strong>Hoàn thành</strong>
              </div>";

    echo json_encode([
        'status' => 'success',
        'maPXK' => $maPXK,
        'html' => $html
    ]);

} catch(Exception $e) {
    $conn->rollback();
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
?>