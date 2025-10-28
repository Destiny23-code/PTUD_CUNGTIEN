<?php
require_once("clsconnect.php");

class LapBCCL extends ketnoi {

    public function insertBaoCaoCL($ngayLap, $nguoiLap, $maLo, $maPhieu, $tieuChi, $ketQuaBaoCao) {
        $link = $this->connect();

        // Escape input để tránh lỗi injection
        $ngayLap_safe      = $link->real_escape_string($ngayLap);
        $nguoiLap_safe     = $link->real_escape_string($nguoiLap);
        $maLo_safe         = $link->real_escape_string($maLo);
        $maPhieu_safe      = $link->real_escape_string($maPhieu);
        $tieuChi_safe      = $link->real_escape_string($tieuChi);
        $ketQuaBaoCao_safe = $link->real_escape_string($ketQuaBaoCao);

        $sql = "
            INSERT INTO BAOCAOCHATLUONG (ngayLap, nguoiLap, tieuChi, maLo, maPhieu, ketQuaBaoCao)
            VALUES (
                '$ngayLap_safe',
                '$nguoiLap_safe',
                '$tieuChi_safe',
                '$maLo_safe',
                '$maPhieu_safe',
                '$ketQuaBaoCao_safe'
            )
        ";

        $result = $this->xuly($link, $sql);
        $link->close();
        return $result;
    }
}
