<?php
class CanhBao
{
    public function soNgayConLai($ngayHetHan)
    {
        if (!$ngayHetHan || $ngayHetHan == "0000-00-00") {
            return null;
        }

        $today = strtotime(date("Y-m-d"));
        $expire = strtotime($ngayHetHan);

        // số ngày còn lại (dương: còn hạn, âm: đã hết hạn)
        $days = ceil(($expire - $today) / 86400);

        return $days;
    }

    // Trả về màu highlight
    public function getRowClass($ngayHetHan)
    {
        $days = $this->soNgayConLai($ngayHetHan);

        if ($days === null) return "";

        // Đã hết hạn
        if ($days < 0) {
            return "table-danger";
        }

        // Sắp hết hạn <= 7 ngày
        if ($days <= 7) {
            return "table-warning";
        }

        return "";
    }

    // Trả về badge cảnh báo
    public function getWarningBadge($ngayHetHan, $trangThai)
    {
        // Chỉ hiển thị cảnh báo nếu trạng thái là "Đã kiểm định"
        if (trim($trangThai) !== "Đã kiểm định") {
            return "";
        }

        $days = $this->soNgayConLai($ngayHetHan);

        if ($days === null) return "";

        if ($days < 0) {
            return "<span class='badge bg-danger ms-1'>⛔ Đã hết hạn</span>";
        }

        if ($days <= 7) {
            return "<span class='badge bg-warning text-dark ms-1'>⚠ Sắp hết hạn ($days ngày)</span>";
        }

        return "";
    }
}
