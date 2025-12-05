<?php
session_start();
require_once('../../class/clsconnect.php');
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['hoTen']) || !isset($_SESSION['maNV'])) {
    echo json_encode(array('status' => 'error', 'message' => 'Chưa đăng nhập'));
    exit;
}

$dsLo     = isset($_POST['dsLo']) ? $_POST['dsLo'] : array();
$ngayNhap = isset($_POST['ngayLap']) ? $_POST['ngayLap'] : date('Y-m-d');

if (empty($dsLo)) {
    echo json_encode(array('status' => 'error', 'message' => 'Không có lô nào để nhập kho'));
    exit;
}

$maNguoiLap  = (int)$_SESSION['maNV'];
$tenNguoiLap = $_SESSION['hoTen'];

$ketnoi_instance = new ketnoi();
$conn = $ketnoi_instance->connect();
$conn->autocommit(false);

try {
    // Tạo phiếu nhập
    $sql = "INSERT INTO phieunhapkho (ngayNhap, nguoiLap, tenNguoiLap, tongSoLuongNhap) 
            VALUES ('$ngayNhap', $maNguoiLap, '$tenNguoiLap', 0)";
    $conn->query($sql);
    $maPNK = $conn->insert_id;
    $maPhieuHienThi = "PNK" . sprintf("%04d", $maPNK);

    $tongSL = 0;
    $chiTiet = array();

    foreach ($dsLo as $maLo) {
        $maLo = (int)$maLo;

        $q = $conn->query("SELECT l.soLuong, l.ngaySX, l.maSP, sp.tenSP 
                           FROM losanpham l 
                           JOIN sanpham sp ON l.maSP = sp.maSP 
                           WHERE l.maLo = $maLo LIMIT 1");
        if (!$q || $q->num_rows == 0) continue;

        $lo = $q->fetch_assoc();
        $sl = (int)$lo['soLuong'];
        $tongSL += $sl;

        // Ghi chi tiết phiếu nhập
        $conn->query("INSERT INTO chitiet_phieunhapkho (maPNK, maLo, soLuongNhap) 
                      VALUES ($maPNK, $maLo, $sl)");

        // Cộng tồn kho sản phẩm
        $conn->query("UPDATE sanpham SET soLuongTon = soLuongTon + $sl 
                      WHERE maSP = " . (int)$lo['maSP']);

        $chiTiet[] = array(
            'maLo'    => $maLo,
            'maSP'    => $lo['maSP'],
            'tenSP'   => $lo['tenSP'],
            'ngaySX'  => date('d/m/Y', strtotime($lo['ngaySX'])),
            'soLuong' => $sl
        );
    }

    // Cập nhật tổng
    $conn->query("UPDATE phieunhapkho SET tongSoLuongNhap = $tongSL WHERE maPNK = $maPNK");
    $conn->commit();

    // —————— PHIẾU NHẬP KHO ĐẸP, CÓ NGÀY SX, KHÔNG CÓ CỘT QC ——————
    $html = "<div class='text-center mb-4'>
                <h2 class='text-success fw-bold'>PHIẾU NHẬP KHO THÀNH PHẨM</h2>
                <h3 class='text-primary fw-bold'>$maPhieuHienThi</h3>
                <p class='fs-5'>Ngày nhập: <strong>" . date('d/m/Y H:i') . "</strong> | Người lập: <strong>$tenNguoiLap</strong></p>
             </div>";

    $html .= "<table class='table table-bordered table-hover text-center align-middle'>
                <thead class='table-success'>
                    <tr>
                        <th>Mã lô</th>
                        <th>Mã SP</th>
                        <th>Tên sản phẩm</th>
                        <th>Ngày SX</th>
                        <th>Số lượng</th>
                    </tr>
                </thead>
                <tbody>";

    foreach ($chiTiet as $ct) {
        $html .= "<tr>
                    <td class='fw-bold fs-5'>{$ct['maLo']}</td>
                    <td>{$ct['maSP']}</td>
                    <td>" . htmlspecialchars($ct['tenSP']) . "</td>
                    <td>{$ct['ngaySX']}</td>
                    <td class='text-primary fw-bold fs-4'>" . number_format($ct['soLuong']) . "</td>
                  </tr>";
    }

    $html .= "<tr class='table-info fw-bold fs-4'>
                <td colspan='4' class='text-end'>TỔNG CỘNG:</td>
                <td class='text-primary'>" . number_format($tongSL) . " sp</td>
              </tr>
              </tbody>
              </table>";

    $html .= "<div class='alert alert-success text-center mt-4 fw-bold fs-5'>
                Nhập kho thành công! Các lô đã được đưa vào kho thành phẩm.
              </div>";

    echo json_encode(array('status' => 'success', 'html' => $html));

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(array('status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()));
}
$conn->close();
?>