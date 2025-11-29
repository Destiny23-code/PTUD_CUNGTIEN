<?php
session_start();
require_once('../../class/clsconnect.php');
header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['hoTen']) || !isset($_SESSION['maNV'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập hoặc thiếu thông tin']);
    exit;
}

$dsLo     = $_POST['dsLo'] ?? [];
$ngayNhap = $_POST['ngayLap'] ?? date('Y-m-d');

if (empty($dsLo)) {
    echo json_encode(['status' => 'error', 'message' => 'Không có lô nào để nhập kho']);
    exit;
}

// Lấy thông tin người lập
$maNguoiLap  = (int)$_SESSION['maNV'];
$tenNguoiLap = $_SESSION['hoTen']; // Tên đầy đủ để lưu vào DB

// Kết nối CSDL
$ketnoi_instance = new ketnoi();
$conn = $ketnoi_instance->connect();
$conn->autocommit(false);

try {
    // 1. Tạo phiếu nhập kho (lưu cả mã + tên người lập)
    $stmt = $conn->prepare("INSERT INTO phieunhapkho 
        (ngayNhap, nguoiLap, tenNguoiLap, tongSoLuongNhap) 
        VALUES (?, ?, ?, 0)");
    $stmt->bind_param("sis", $ngayNhap, $maNguoiLap, $tenNguoiLap);
    $stmt->execute();
    $maPNK = $conn->insert_id;
    $maPhieuHienThi = "PNK" . sprintf("%04d", $maPNK);

    $tongSL = 0;
    $chiTiet = [];

    foreach ($dsLo as $maLo) {
        $maLo = (int)$maLo;

        $q = $conn->query("SELECT l.soLuong, l.maSP, sp.tenSP 
                           FROM losanpham l 
                           JOIN sanpham sp ON l.maSP = sp.maSP 
                           WHERE l.maLo = $maLo LIMIT 1");
        if (!$q || $q->num_rows == 0) continue;

        $lo = $q->fetch_assoc();
        $sl = (int)$lo['soLuong'];
        $tongSL += $sl;

        // Ghi chi tiết nhập kho
        $conn->query("INSERT INTO chitiet_phieunhapkho (maPNK, maLo, soLuongNhap) 
                      VALUES ($maPNK, $maLo, $sl)");

        // Cộng tồn kho
        $conn->query("UPDATE sanpham SET soLuongTon = soLuongTon + $sl 
                      WHERE maSP = {$lo['maSP']}");

        $chiTiet[] = [
            'maLo'    => $maLo,
            'maSP'    => $lo['maSP'],
            'tenSP'   => $lo['tenSP'],
            'soLuong' => $sl
        ];
    }

    // Cập nhật tổng số lượng
    $conn->query("UPDATE phieunhapkho SET tongSoLuongNhap = $tongSL WHERE maPNK = $maPNK");
    $conn->commit();

    // === TẠO PHIẾU NHẬP KHO ĐẸP (hiện tên người lập) ===
    $html = "<div class='text-center mb-4'>
                <h2 class='text-success fw-bold'>PHIẾU NHẬP KHO $maPhieuHienThi</h2>
                <p class='fs-5'>
                    Ngày nhập: <strong>" . date('d/m/Y H:i') . "</strong> | 
                    Người lập: <strong>$tenNguoiLap</strong>
                </p>
             </div>";

    $html .= "<table class='table table-bordered table-hover'>
                <thead class='table-success text-center'>
                    <tr>
                        <th>Mã lô</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Số lượng</th>
                    </tr>
                </thead>
                <tbody>";

    foreach ($chiTiet as $ct) {
        $html .= "<tr class='text-center align-middle'>
                    <td class='fw-bold'>{$ct['maLo']}</td>
                    <td>{$ct['maSP']}</td>
                    <td>{$ct['tenSP']}</td>
                    <td class='fw-bold text-primary fs-5'>" . number_format($ct['soLuong']) . "</td>
                  </tr>";
    }

    $html .= "<tr class='table-info fw-bold fs-4'>
                <td colspan='3' class='text-end pe-4'>TỔNG CỘNG:</td>
                <td class='text-center text-primary'>" . number_format($tongSL) . " sp</td>
              </tr>
              </tbody>
             </table>";

    $html .= "<div class='alert alert-success text-center mt-4 fw-bold'>
                Nhập kho thành công! Các lô đã được nhập vào kho thành phẩm.
              </div>";

    echo json_encode(['status' => 'success', 'html' => $html]);

} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>