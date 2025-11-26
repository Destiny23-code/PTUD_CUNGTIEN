<?php
require_once('../../class/session_init.php');
require_once('../../class/clsconnect.php');

if (!isset($_SESSION['hoTen'])) {
    header('Location: ../../pages/dangnhap/dangnhap.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request');
}

$dsLo     = $_POST['dsLo'] ?? [];
$maNguoiLap = (int)($_POST['nguoiLap'] ?? 0);  // BÂY GIỜ LÀ MÃ NHÂN VIÊN
$ngayNhap = $_POST['ngayLap'] ?? date('Y-m-d');

if (empty($dsLo) || $maNguoiLap <= 0) {
    echo "<script>alert('Dữ liệu không hợp lệ!'); window.history.back();</script>";
    exit;
}

$conn = (new ketnoi())->connect();
$conn->autocommit(false);

try {
    // 1. Tạo phiếu nhập kho (nguoiLap giờ là INT)
    $stmt = $conn->prepare("INSERT INTO phieunhapkho (ngayNhap, nguoiLap, tongSoLuongNhap) VALUES (?, ?, 0)");
    $stmt->bind_param("si", $ngayNhap, $maNguoiLap);  // s = string, i = int
    $stmt->execute();
    $maPNK = $conn->insert_id;

    $tongSL = 0;

    foreach ($dsLo as $maLo) {
        $maLo = (int)$maLo;

        // Lấy thông tin lô
        $q = $conn->query("SELECT soLuong, maSP FROM losanpham WHERE maLo = $maLo");
        if (!$q || $q->num_rows == 0) continue;

        $lo = $q->fetch_assoc();
        $soLuongNhap = $lo['soLuong'];
        $tongSL += $soLuongNhap;

        // Ghi chi tiết phiếu nhập
        $stmt2 = $conn->prepare("INSERT INTO chitiet_phieunhapkho (maPNK, maLo, soLuongNhap) VALUES (?, ?, ?)");
        $stmt2->bind_param("iii", $maPNK, $maLo, $soLuongNhap);
        $stmt2->execute();

        // Cộng tồn kho thành phẩm
        $conn->query("UPDATE sanpham SET soLuongTon = soLuongTon + $soLuongNhap WHERE maSP = " . $lo['maSP']);
    }

    // Cập nhật tổng số lượng
    $conn->query("UPDATE phieunhapkho SET tongSoLuongNhap = $tongSL WHERE maPNK = $maPNK");

    $conn->commit();

    echo "<script>
            alert('Nhập kho thành công!\\nMã phiếu: PNK" . sprintf("%04d", $maPNK) . 
                  "\\nTổng nhập: " . number_format($tongSL) . " sản phẩm');
            window.location.href = 'nhapkho.php';
          </script>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<script>alert('Lỗi nhập kho: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
}
?>