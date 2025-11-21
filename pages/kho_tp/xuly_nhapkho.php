<?php
require_once('../../class/session_init.php');
require_once('../../class/clsconnect.php');
header('Content-Type: application/json');

if (!isset($_SESSION['hoTen'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$items = $input['items'] ?? [];

if (empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'Không có dữ liệu xuất kho']);
    exit;
}

$conn = (new ketnoi())->connect();
$conn->autocommit(false);

try {
    $ngayXuat = date('Y-m-d');
    $nguoiLap = $_SESSION['hoTen'];  // hoặc $_SESSION['maNV'] nếu cần

    // Tạo phiếu xuất kho mới
    $conn->query("INSERT INTO phieuxuatkho (nguoiLap, ngayLap, ngayXuat, trangThai) 
                  VALUES (1, '$ngayXuat', '$ngayXuat', 'Đã xuất')");  // tạm dùng 1, bạn có thể đổi thành maNV
    $maPXK = $conn->insert_id;

    $html = "<p class='fw-bold fs-5'>PHIẾU XUẤT KHO PXK" . sprintf("%04d", $maPXK) . 
            " &nbsp; | &nbsp; Ngày xuất: " . date('d/m/Y') . 
            " &nbsp; | &nbsp; Người xuất: " . htmlspecialchars($nguoiLap) . "</p>";
    $html .= "<table class='table table-bordered table-sm'><thead class='table-success'>
              <tr><th>ĐH</th><th>Khách hàng</th><th>Sản phẩm</th><th>SL xuất</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>";

    $tongSL = 0;
    $tongTien = 0;

    foreach ($items as $item) {
        $maDH = (int)$item['maDH'];
        $maSP = (int)$item['maSP'];
        $sl   = (int)$item['soLuong'];

        // Lấy thông tin chi tiết
        $q = $conn->query("SELECT ct.donGia, kh.tenKH, sp.tenSP, sp.soLuongTon 
                           FROM chitiet_donhang ct
                           JOIN khachhang kh ON ct.maKH = kh.maKH
                           JOIN sanpham sp ON ct.maSP = sp.maSP
                           WHERE ct.maDH = $maDH AND ct.maSP = $maSP LIMIT 1");
        $r = $q->fetch_assoc();

        if ($sl > $r['soLuongTon']) {
            throw new Exception("Tồn kho {$r['tenSP']} không đủ (còn {$r['soLuongTon']})");
        }

        $thanhTien = $sl * $r['donGia'];

        // Ghi chi tiết phiếu xuất
        $tenKH_esc = $conn->real_escape_string($r['tenKH']);
        $tenSP_esc = $conn->real_escape_string($r['tenSP']);
        $conn->query("INSERT INTO chitiet_phieuxuatkho 
                     (maPXK, maDH, maSP, soLuongXuat, ghiChu, maLo, tenKH, tenSP)
                     VALUES ($maPXK, $maDH, $maSP, $sl, NULL, 0, '$tenKH_esc', '$tenSP_esc')");

        // Trừ tồn kho tổng
        $conn->query("UPDATE sanpham SET soLuongTon = soLuongTon - $sl WHERE maSP = $maSP");

        $html .= "<tr>
                    <td>$maDH</td>
                    <td>" . htmlspecialchars($r['tenKH']) . "</td>
                    <td>{$r['tenSP']}</td>
                    <td class='text-end'>$sl</td>
                    <td class='text-end'>" . number_format($r['donGia']) . "</td>
                    <td class='text-end'>" . number_format($thanhTien) . "</td>
                  </tr>";

        $tongSL += $sl;
        $tongTien += $thanhTien;
    }

    $html .= "<tr class='table-info fw-bold'>
                <td colspan='3'>TỔNG CỘNG</td>
                <td class='text-end'>".number_format($tongSL)."</td>
                <td></td>
                <td class='text-end'>".number_format($tongTien)."</td>
              </tr></tbody></table>";

    // Cập nhật tổng số lượng phiếu (nếu bạn thêm cột này)
    $conn->query("UPDATE phieuxuatkho SET trangThai = 'Đã xuất' WHERE maPXK = $maPXK");

    // Tự động hoàn thành đơn hàng nếu đã xuất đủ
    foreach ($items as $item) {
        $maDH = $item['maDH'];
        $check = $conn->query("SELECT 
                                SUM(ct.soLuong) AS tongDat,
                                COALESCE(SUM(px.soLuongXuat),0) AS tongXuat
                               FROM chitiet_donhang ct
                               LEFT JOIN chitiet_phieuxuatkho px ON ct.maDH=px.maDH AND ct.maSP=px.maSP
                               WHERE ct.maDH = $maDH");
        $c = $check->fetch_assoc();
        if ($c['tongDat'] <= $c['tongXuat']) {
            $conn->query("UPDATE donhang SET trangThai = 'Hoàn thành' WHERE maDH = $maDH");
        }
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'maPXK' => sprintf("%04d", $maPXK),
        'html' => $html
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>