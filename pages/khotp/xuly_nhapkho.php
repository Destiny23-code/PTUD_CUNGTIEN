<?php
session_start();
require_once("../../class/clsNhapKho.php");
require_once("../../class/clsconnect.php");

// Kiểm tra đăng nhập và dữ liệu gửi lên
if (!isset($_SESSION['maNV'])) {
    echo "<script>alert('Vui lòng đăng nhập để thực hiện chức năng này!'); window.location='../../pages/dangnhap/dangnhap.php';</script>";
    exit;
}

if (!isset($_POST["dsLo"]) || !is_array($_POST["dsLo"]) || count($_POST["dsLo"]) == 0) {
    echo "<script>alert('Vui lòng chọn ít nhất một lô sản phẩm!'); history.back();</script>";
    exit;
}

// Lấy mã nhân viên từ session thay vì dùng tên
$nguoiLap = $_SESSION['maNV'];
$ngayLap = $_POST["ngayLap"];
$maPhieu = $_POST["maPhieu"];
$dsLo = $_POST["dsLo"];

if (empty($maPhieu) || empty($ngayLap)) {
    echo "<script>alert('Vui lòng điền đầy đủ thông tin phiếu nhập!'); history.back();</script>";
    exit;
}

$conn = (new ketnoi())->connect();
if (!$conn) {
    echo "<script>alert('Lỗi kết nối cơ sở dữ liệu.'); history.back();</script>";
    exit;
}

// Nếu table chi tiết phiếu nhập kho chưa tồn tại, tạo nó tự động
$createCTTable = "CREATE TABLE IF NOT EXISTS `chitiet_phieunhapkho` (
    `maCTPNK` INT NOT NULL AUTO_INCREMENT,
    `maPNK` INT NOT NULL,
    `maLo` INT NOT NULL,
    `soLuongNhap` INT NOT NULL,
    `ghiChu` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`maCTPNK`),
    INDEX (`maPNK`),
    INDEX (`maLo`),
    CONSTRAINT `fk_ctpnk_pnk` FOREIGN KEY (`maPNK`) REFERENCES `phieunhapkho`(`maPNK`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ctpnk_malo` FOREIGN KEY (`maLo`) REFERENCES `losanpham`(`maLo`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if (!$conn->query($createCTTable)) {
    echo "<script>alert('Lỗi tạo bảng chi tiết phiếu nhập kho: " . addslashes($conn->error) . "'); history.back();</script>";
    exit;
}

// Bắt đầu transaction để đảm bảo tính nhất quán
$conn->begin_transaction();

// Lấy và tính tổng số lượng một lần duy nhất
$soLuongMap = array(); // lưu số lượng của từng lô
$tongSL = 0;

// Đọc số lượng của tất cả các lô một lần
$maLoList = implode(',', array_map('intval', $dsLo));
$qr = $conn->query("SELECT maLo, soLuong FROM losanpham WHERE maLo IN ($maLoList)");
if (!$qr) {
    $conn->rollback();
    echo "<script>alert('Lỗi truy vấn khi đọc số lượng lô: " . $conn->error . "'); history.back();</script>";
    exit;
}
while ($row = $qr->fetch_assoc()) {
    $soLuongMap[$row['maLo']] = (int)$row['soLuong'];
    $tongSL += (int)$row['soLuong'];
}

// Tạo phiếu nhập kho
$nguoiLapEsc = $conn->real_escape_string($nguoiLap);
$ngayLapEsc = $conn->real_escape_string($ngayLap);
$sqlInsertPN = "INSERT INTO phieunhapkho(ngayNhap, nguoiLap, tongSoLuongNhap) VALUES('$ngayLapEsc', '$nguoiLapEsc', $tongSL)";
    if (!$conn->query($sqlInsertPN)) {
    $conn->rollback();
    echo "<script>alert('Không thể tạo phiếu nhập kho: " . $conn->error . "'); history.back();</script>";
    exit;
}
$maPNK = $conn->insert_id;

// Lưu chi tiết từng lô và cập nhật tồn kho sản phẩm
foreach ($dsLo as $maLoRaw) {
    $maLo = (int)$maLoRaw;
    if (!isset($soLuongMap[$maLo])) {
        $conn->rollback();
        echo "<script>alert('Lỗi: Không tìm thấy thông tin số lượng của lô " . $maLo . "'); history.back();</script>";
        exit;
    }
    $soLuong = $soLuongMap[$maLo];

    // Thêm chi tiết
    $sqlCT = "INSERT INTO chitiet_phieunhapkho(maPNK, maLo, soLuongNhap) VALUES($maPNK, $maLo, $soLuong)";
    if (!$conn->query($sqlCT)) {
    $conn->rollback();
    echo "<script>alert('Lỗi khi lưu chi tiết phiếu: " . $conn->error . "'); history.back();</script>";
        exit;
    }

    // Cập nhật tồn kho sản phẩm tương ứng với lô
    $sqlUpdate = "UPDATE sanpham sp 
                  JOIN losanpham l ON sp.maSP = l.maSP 
                  SET sp.soLuongTon = sp.soLuongTon + $soLuong
                  WHERE l.maLo = $maLo";
    if (!$conn->query($sqlUpdate)) {
    $conn->rollback();
    echo "<script>alert('Lỗi khi cập nhật tồn kho: " . $conn->error . "'); history.back();</script>";
        exit;
    }
}

// Nếu mọi thứ ok, commit
if (!$conn->commit()) {
    $conn->rollback();
    echo "<script>alert('Lỗi khi lưu giao dịch (commit thất bại).'); history.back();</script>";
    exit;
}

echo "<script>alert('Phiếu nhập kho đã được lập thành công!'); window.location='nhapkho.php';</script>";
exit;
?>