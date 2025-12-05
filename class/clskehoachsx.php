<?php
require_once("clsconnect.php"); 

class KeHoachModel extends ketnoi {
    /**
     * Lấy danh sách Đơn hàng chờ lập kế hoạch.
     * @return array Mảng chứa dữ liệu đơn hàng.
     */
    public function getDSDonHang($raw_maDH, $raw_ngayDat, $raw_trangThai) {
        $link = $this->connect();
        
        // 1. Làm sạch dữ liệu đầu vào (BẮT BUỘC)
        // Model phải tự chịu trách nhiệm làm sạch dữ liệu của nó
        $maDH = $link->real_escape_string($raw_maDH);
        $ngayDat = $link->real_escape_string($raw_ngayDat);
        $trangThai = $link->real_escape_string($raw_trangThai);
        
        // 2. XÂY DỰNG MỆNH ĐỀ WHERE
        // (Đây là logic bạn chuyển từ dsdh.php sang)
        $where_clauses = array(); 
        if (!empty($maDH)) {
            $where_clauses[] = "maDH LIKE '%{$maDH}%'";
        }
        if (!empty($ngayDat)) {
            $where_clauses[] = "ngayDat = '{$ngayDat}'";
        }
        if (!empty($trangThai)) {
            $where_clauses[] = "trangThai = '{$trangThai}'";
        }
        
        $where = '';
        if (count($where_clauses) > 0) {
            $where = ' WHERE ' . implode(' AND ', $where_clauses);
        }
        
        // 3. XÂY DỰNG CÂU TRUY VẤN SQL HOÀN CHỈNH
        $sql = "SELECT maDH, maKH, ngayDat, ngayGiaoDuKien, trangThai, ghiChu 
                FROM DONHANG 
                {$where}
                ORDER BY ngayDat DESC";

        // 4. Lấy dữ liệu, đóng kết nối và trả về
        $data = $this->laydulieu($link, $sql); 
        $link->close();
        
        return $data;
    }
    public function getDSDonHangCho() {
        $link = $this->connect();
        $sql = "SELECT 
                d.maDH, 
                d.ngayDat, 
                d.ngayGiaoDuKien, 
                d.trangThai, kh.tenKH, kh.diaChi, kh.sDT, kh.email
            FROM DONHANG d
            JOIN KHACHHANG kh ON d.maKH = kh.maKH
            WHERE d.trangThai = N'Mới tạo'
            ORDER BY d.ngayDat DESC";
        
        $data = $this->laydulieu($link, $sql);
        $link->close();
        return $data;
    }
    /**
     * ✅ Lấy danh sách sản phẩm theo mã đơn hàng
     * @param string $maDH Mã đơn hàng
     * @return array Danh sách sản phẩm trong đơn hàng
     */
    public function getSanPhamTheoDonHang($maDH) {
        $link = $this->connect();
        $maDH_safe = $link->real_escape_string($maDH);

        $sql = "SELECT 
                    c.maSP, s.tenSP, s.loaiSP, s.donViTinh, s.moTa, c.soLuong
                FROM CHITIET_DONHANG c
                JOIN SANPHAM s ON c.maSP = s.maSP
                WHERE c.maDH = '$maDH_safe'";

        $data = $this->laydulieu($link, $sql);
        $link->close();

        // Nếu không có dữ liệu, trả về mảng rỗng
        if (!is_array($data)) {
            $data = array();
        }

        return $data;
    }
    // Lấy chi tiết đơn hàng (thông tin + sản phẩm + khách hàng)
    public function getChiTietDonHang($maDH) {
        $link = $this->connect();
        $maDH_safe = $link->real_escape_string($maDH);

        $sqlInfo = "SELECT 
                        d.maDH, d.ngayDat, d.ngayGiaoDuKien, d.trangThai, d.ghiChu,
                        kh.tenKH, kh.diaChi, kh.email, kh.sDT
                    FROM DONHANG d
                    JOIN KHACHHANG kh ON d.maKH = kh.maKH
                    WHERE d.maDH = '$maDH_safe'";
        $thongtin = $this->laydulieu($link, $sqlInfo);
        $thongtin = is_array($thongtin) && count($thongtin) > 0 ? $thongtin[0] : array();

        $sqlSP = "SELECT 
                        c.maSP, s.tenSP, s.loaiSP, s.donViTinh, c.soLuong
                    FROM CHITIET_DONHANG c
                    JOIN SANPHAM s ON c.maSP = s.maSP
                    WHERE c.maDH = '$maDH_safe'";
        $sanpham = $this->laydulieu($link, $sqlSP);

        $link->close();
        return array(
            'thongtin' => $thongtin,
            'sanpham'  => $sanpham
        );
    }
    public function getNguyenLieuTheoSanPham($maSP) {
        $link = $this->connect();
        $maSP_safe = $link->real_escape_string($maSP);

        $sql = "SELECT 
                    nlsp.maNL, 
                    nl.tenNL,
                    nl.soLuongTon,
                    nlsp.soLuongTheoSP,
                    nl.donViTinh
                FROM dinhmucnguyenlieu nlsp
                JOIN nguyenlieu nl ON nlsp.maNL = nl.maNL
                WHERE nlsp.maSP = '$maSP_safe'";

        $data = $this->laydulieu($link, $sql);
        $link->close();

        return is_array($data) ? $data : array();
    }

    public function insertKeHoachSX($maDH, $nguoiLap, $ngayLap, $ngayBatDau, $ngayKetThuc, $ghiChu) {
        $link = $this->connect();
        $maDH_safe = $link->real_escape_string($maDH);
        $nguoiLap_safe = $link->real_escape_string($nguoiLap);
        $ghiChu_safe = $link->real_escape_string($ghiChu);

        $sql = "INSERT INTO KEHOACHSANXUAT (maDH, nguoiLap, ngayLap, ngayBDDK, ngayKTDK, trangThai, ghiChu)
                VALUES (
                    '$maDH_safe', 
                    '$nguoiLap_safe', 
                    '$ngayLap', 
                    '$ngayBatDau', 
                    '$ngayKetThuc', 
                    N'Chờ phê duyệt', 
                    N'$ghiChu_safe'
                )";

        // Thực thi và kiểm tra kết quả
        if ($this->xuly($link, $sql)) {
            // Lấy mã kế hoạch vừa thêm (auto increment)
            $maKHSX = $link->insert_id;
            $link->close();
            return $maKHSX;
        } else {
            $link->close();
            return false;
        }
    }

    public function insertChiTietNguyenLieuKHSX($maKHSX, $maSP, $maNL, $soLuong1SP, $tongSLCan, $slTonTaiKho, $slThieuHut, $phuongAn, $maLo) {
        $link = $this->connect();

        $maKHSX_safe = $link->real_escape_string($maKHSX);
        $maSP_safe = $link->real_escape_string($maSP);
        $maNL_safe = $link->real_escape_string($maNL);
        $phuongAn_safe = $link->real_escape_string($phuongAn);
        $maLo_safe = $link->real_escape_string($maLo); 
        
        $soLuong1SP_safe = (string)$soLuong1SP;
        $tongSLCan_safe = (float)$tongSLCan;
        $slTonTaiKho_safe = (float)$slTonTaiKho;
        $slThieuHut_safe = (float)$slThieuHut;

        $sql = "INSERT INTO CHITIET_KHSX (
                    maKHSX, maSP, maNL, soLuong1SP, tongSLCan, slTonTaiKho, slThieuHut, phuongAnXuLy, maLo
                ) VALUES (
                    '$maKHSX_safe',
                    '$maSP_safe',
                    '$maNL_safe',
                    '$soLuong1SP_safe',
                    $tongSLCan_safe,
                    $slTonTaiKho_safe,
                    $slThieuHut_safe,
                    '$phuongAn_safe',
                    '$maLo_safe'
                )";

        $result = $this->xuly($link, $sql);
        $link->close();
        return $result;
    }

    public function insertLoSanPham($maLo, $maKHSX, $maSP, $ngaySX, $soLuong) {
        $link = $this->connect();
        $maLo_safe = $link->real_escape_string($maLo);
        //$maKHSX_safe = $link->real_escape_string($maKHSX);
        $maSP_safe = $link->real_escape_string($maSP);
        $soLuong_safe = (int)$soLuong;

        $sql = "INSERT INTO losanpham (maLo, maSP, ngaySX, soLuong, trangThai )
                VALUES ('$maLo_safe', '$maSP_safe', '$ngaySX',$soLuong_safe, N'Đang xử lý')";

        $res = $this->xuly($link, $sql);
        $link->close();
        return $res;
    }

    /**
     * Cập nhật trạng thái của Đơn hàng.
     */
    public function updateTrangThaiDonHang($maDH, $trangThaiMoi) {
        $link = $this->connect();
        $maDH_safe = $link->real_escape_string($maDH);
        $trangThaiMoi_safe = $link->real_escape_string($trangThaiMoi);

        $sql = "UPDATE DONHANG 
                SET trangThai = N'$trangThaiMoi_safe'
                WHERE maDH = '$maDH_safe'";

        $result = $this->xuly($link, $sql);
        $link->close();
        return $result;
    }

    /**
     * ✅ Lấy danh sách kế hoạch sản xuất
     */
    public function getDSKeHoach($raw_maKH = '', $raw_ngayLap = '', $raw_trangThai = '') {
        $link = $this->connect();
        
        // 1️⃣ Làm sạch dữ liệu đầu vào
        $maKH = $link->real_escape_string($raw_maKH);
        $ngayLap = $link->real_escape_string($raw_ngayLap);
        $trangThai = $link->real_escape_string($raw_trangThai);
        
        // 2️⃣ Xây dựng mệnh đề WHERE
        $where_clauses = array();
        if (!empty($maKH)) {
            $where_clauses[] = "maKHSX LIKE '%{$maKH}%'";
        }
        if (!empty($ngayLap)) {
            $where_clauses[] = "DATE(ngayLap) = '{$ngayLap}'";
        }
        if (!empty($trangThai)) {
            $where_clauses[] = "LOWER(trangThai) = LOWER('{$trangThai}')";
        }
        
        $where = '';
        if (count($where_clauses) > 0) {
            $where = ' WHERE ' . implode(' AND ', $where_clauses);
        }
        
        // 3️⃣ Xây dựng câu truy vấn SQL (THÊM $where vào đây)
        $sql = "SELECT maKHSX, maDH, nguoiLap, ngayLap, ngayBDDK, ngayKTDK, trangThai, ghiChu, lyDoTuChoi
                FROM KEHOACHSANXUAT" . $where . " ORDER BY ngayLap DESC";

        // DEBUG: Hiển thị câu SQL để kiểm tra
        // echo "SQL: " . $sql; // Bỏ comment dòng này để debug
        
        // 4️⃣ Lấy dữ liệu, đóng kết nối và trả về
        $data = $this->laydulieu($link, $sql);
        $link->close();
        
        return $data;
    }



    public function getDanhSachKeHoach() {
        $link = $this->connect();
        $sql = "SELECT maKHSX, maDH, nguoiLap, ngayLap, ngayBDDK, ngayKTDK, trangThai, ghiChu, lyDoTuChoi
                FROM KEHOACHSANXUAT
                ORDER BY ngayLap DESC";

        $data = $this->laydulieu($link, $sql); 
        $link->close();
        
        return $data;
    }

    /**
     * ✅ Lấy chi tiết kế hoạch sản xuất theo mã kế hoạch
     */
    /*public function getChiTietKeHoach($maKHSX) {
        $link = $this->connect();
        $maKHSX_safe = mysqli_real_escape_string($link, $maKHSX);

        // Lấy thông tin kế hoạch
        $sqlKH = "SELECT * FROM KEHOACHSANXUAT WHERE maKHSX='$maKHSX_safe'";
        $resKH = mysqli_query($link, $sqlKH);
        $info = ($resKH && mysqli_num_rows($resKH) > 0) ? mysqli_fetch_assoc($resKH) : array();

        $dsSP = array();
        $dsNL = array();

        if (!empty($info)) {
            // Lấy sản phẩm trong đơn hàng
            $sqlSP = "SELECT cdh.maSP, s.tenSP, s.donViTinh, cdh.soLuong
                      FROM CHITIET_DONHANG cdh
                      JOIN SANPHAM s ON cdh.maSP = s.maSP
                      WHERE cdh.maDH='" . mysqli_real_escape_string($link, $info['maDH']) . "'";
            $resSP = mysqli_query($link, $sqlSP);
            if ($resSP) {
                while ($r = mysqli_fetch_assoc($resSP)) {
                    $dsSP[] = $r;
                }
                mysqli_free_result($resSP);
            }

            // Lấy nguyên liệu cho kế hoạch
            $sqlNL = "SELECT nlct.maKHSX, nlct.maSP, nlct.maNL, nl.tenNL, nl.donViTinh,
                             nlct.soLuong1SP, nlct.tongSLCan, nlct.slTonTaiKho, nlct.slThieuHut, nlct.phuongAnXuLy, nlct.maLo
                      FROM CHITIET_KHSX nlct
                      LEFT JOIN NGUYENLIEU nl ON nlct.maNL = nl.maNL
                      WHERE nlct.maKHSX='$maKHSX_safe'";
            $resNL = mysqli_query($link, $sqlNL);
            if ($resNL) {
                while ($r = mysqli_fetch_assoc($resNL)) {
                    $dsNL[] = $r;
                }
                mysqli_free_result($resNL);
            }
        }

        mysqli_close($link);
        return array(
            'thongtin'   => $info,
            'sanpham'    => $dsSP,
            'nguyenlieu' => $dsNL
        );
    }*/

    /**
     * ✅ Cập nhật trạng thái kế hoạch sản xuất
     */
    public function capNhatTrangThaiKeHoach($maKHSX, $trangThai, $lyDo = null) {
        $link = $this->connect();
        $maKHSX_safe = mysqli_real_escape_string($link, $maKHSX);
        $trangThai_safe = mysqli_real_escape_string($link, $trangThai);
        $lyDo_safe = mysqli_real_escape_string($link, $lyDo);

        if ($trangThai_safe == 'Từ chối') {
            $sql = "UPDATE KEHOACHSANXUAT 
                    SET trangThai='$trangThai_safe', lyDoTuChoi='$lyDo_safe'
                    WHERE maKHSX='$maKHSX_safe'";
        } else {
            $sql = "UPDATE KEHOACHSANXUAT 
                    SET trangThai='$trangThai_safe', lyDoTuChoi=NULL
                    WHERE maKHSX='$maKHSX_safe'";
        }

        $ok = mysqli_query($link, $sql);
        mysqli_close($link);
        return $ok ? true : false;
    }

    
    public function thongKeKHSXPheDuyet() {
        $link = $this->connect();
        $sql = "SELECT 
                    COUNT(maKHSX) AS TongKH,
                    SUM(CASE WHEN trangThai = N'Chờ phê duyệt' THEN 1 ELSE 0 END) AS ChoPheDuyet,
                    SUM(CASE WHEN trangThai = N'Đã duyệt' THEN 1 ELSE 0 END) AS DaDuyet,
                    SUM(CASE WHEN trangThai = N'Từ chối' THEN 1 ELSE 0 END) AS TuChoi
                FROM KEHOACHSANXUAT";
        $data = $this->laydulieu($link, $sql);
        $link->close();
        return is_array($data) && count($data) > 0 ? $data[0] : array(); 
    }

    public function getDanhSachKeHoachChoBaoCao() {
        $link = $this->connect();
        $sql = "SELECT 
                    maKHSX, 
                    maDH,
                    ngayLap, 
                    trangThai
                FROM KEHOACHSANXUAT
                ORDER BY ngayLap DESC";
        $data = $this->laydulieu($link, $sql);
        $link->close();
        return is_array($data) ? $data : array();
    }
    
    /*Báo cáo chất lượng*/
    public function tinhTyLeChatLuong() {
        $link = $this->connect();
        
        $sql = "SELECT 
                    COUNT(maPKD) AS TongPhieu,
                    SUM(CASE WHEN ketQuaBaoCao = N'Đạt' THEN 1 ELSE 0 END) AS Dat,
                    SUM(CASE WHEN ketQuaBaoCao = N'Không đạt' THEN 1 ELSE 0 END) AS KhongDat
                FROM PHIEUBAOCAOCHATLUONG
                WHERE ketQuaBaoCao IN (N'Đạt', N'Không đạt')";
        
        $data = $this->laydulieu($link, $sql);
        $link->close();
        
        $tong = 0;
        $dat = 0;
        $loi = 0;
        
        if (is_array($data) && count($data) > 0) {
            $tong = isset($data[0]['TongPhieu']) ? (int)$data[0]['TongPhieu'] : 0;
            $dat = isset($data[0]['Dat']) ? (int)$data[0]['Dat'] : 0;
            $loi = isset($data[0]['KhongDat']) ? (int)$data[0]['KhongDat'] : 0;
        }
        
        if ($tong > 0) {
            return array(
                'TyLeDat' => $dat / $tong,
                'TyLeLoi' => $loi / $tong,
                'Dat' => $dat,
                'Loi' => $loi
            );
        }
        
        return array('TyLeDat' => 0, 'TyLeLoi' => 0, 'Dat' => 0, 'Loi' => 0);
    }

    public function getPhieuBaoCaoMoiNhat() {
        $link = $this->connect();
        
        $sql = "SELECT p.*, nv.tenNV 
                FROM PHIEUBAOCAOCHATLUONG p
                LEFT JOIN NHANVIEN nv ON p.nguoiLap = nv.iduser
                ORDER BY p.maPKD DESC 
                LIMIT 1";
        
        $data = $this->laydulieu($link, $sql);
        $link->close();
        
        if (is_array($data) && count($data) > 0) {
            $row = $data[0];
            if (!empty($row['tenNV'])) {
                $row['nguoiLap'] = $row['tenNV'];
            }
            return $row;
        }
        return null;
    }
    // ---Báo cáo sản lượng---
    public function thongKeSanLuongTheoLoaiSP() {
        $link = $this->connect();
        $sql = "SELECT 
                    sp.loaiSP, 
                    SUM(ctdh.soLuong) AS TongSanLuong
                FROM CHITIET_DONHANG ctdh
                JOIN SANPHAM sp ON ctdh.maSP = sp.maSP
                JOIN DONHANG dh ON ctdh.maDH = dh.maDH
                WHERE dh.trangThai = N'Hoàn thành'
                GROUP BY sp.loaiSP";
        $data = $this->laydulieu($link, $sql);
        $link->close();
        return is_array($data) ? $data : array();
    }

    public function getSanLuongTheoThang($year = null) {
        $currentYear = (!is_null($year) && $year != '') ? $year : date('Y');
        
        $link = $this->connect();
        $sql = "SELECT 
                    MONTH(ngaySX) AS Thang, 
                    SUM(soLuong) AS TongSanLuong
                FROM LOSANPHAM
                WHERE YEAR(ngaySX) = " . (int)$currentYear . "
                GROUP BY MONTH(ngaySX)
                ORDER BY Thang";
        $data = $this->laydulieu($link, $sql);
        $link->close();
        return is_array($data) ? $data : array();
    }

    public function getHieuSuatLaoDong() {
        // Chỉ số GIẢ ĐỊNH
        return array(
            array('Thang' => 7, 'HieuSuat' => 84),
            array('Thang' => 8, 'HieuSuat' => 87),
            array('Thang' => 9, 'HieuSuat' => 89),
        );
    }

    public function getLoSanPhamTheoKHSXVaSP($maKHSX, $maSP) {
        $link = $this->connect();
        $maKHSX_safe = $link->real_escape_string($maKHSX);
        $maSP_safe = $link->real_escape_string($maSP);
        
        // Lấy các lô sản phẩm từ CHITIET_KHSX (vì bảng losanpham không có maKHSX)
        $sql = "SELECT DISTINCT nlct.maLo, lp.ngaySX, lp.soLuong, lp.trangThai
                FROM CHITIET_KHSX nlct
                LEFT JOIN losanpham lp ON nlct.maLo = lp.maLo
                WHERE nlct.maKHSX = '$maKHSX_safe' 
                AND nlct.maSP = '$maSP_safe'
                ORDER BY nlct.maLo";
        
        $data = $this->laydulieu($link, $sql);
        $link->close();
        
        return is_array($data) ? $data : array();
    }

    public function getChiTietKeHoach($maKHSX) {
        $link = $this->connect();
        $maKHSX_safe = mysqli_real_escape_string($link, $maKHSX);

        // Lấy thông tin kế hoạch
        $sqlKH = "SELECT * FROM KEHOACHSANXUAT WHERE maKHSX='$maKHSX_safe'";
        $resKH = mysqli_query($link, $sqlKH);
        $info = ($resKH && mysqli_num_rows($resKH) > 0) ? mysqli_fetch_assoc($resKH) : array();

        $dsSP = array();
        $dsNL = array();

        if (!empty($info)) {
            // Lấy sản phẩm trong đơn hàng
            $sqlSP = "SELECT cdh.maSP, s.tenSP, s.donViTinh, cdh.soLuong
                    FROM CHITIET_DONHANG cdh
                    JOIN SANPHAM s ON cdh.maSP = s.maSP
                    WHERE cdh.maDH='" . mysqli_real_escape_string($link, $info['maDH']) . "'";
            $resSP = mysqli_query($link, $sqlSP);
            if ($resSP) {
                while ($r = mysqli_fetch_assoc($resSP)) {
                    $dsSP[] = $r;
                }
                mysqli_free_result($resSP);
            }

            // Lấy nguyên liệu cho kế hoạch - THÊM maLo vào SELECT
            $sqlNL = "SELECT nlct.maKHSX, nlct.maSP, nlct.maNL, nl.tenNL, nl.donViTinh,
                            nlct.soLuong1SP, nlct.tongSLCan, nlct.slTonTaiKho, nlct.slThieuHut, 
                            nlct.phuongAnXuLy, nlct.maLo
                    FROM CHITIET_KHSX nlct
                    LEFT JOIN NGUYENLIEU nl ON nlct.maNL = nl.maNL
                    WHERE nlct.maKHSX='$maKHSX_safe'";
            $resNL = mysqli_query($link, $sqlNL);
            if ($resNL) {
                while ($r = mysqli_fetch_assoc($resNL)) {
                    $dsNL[] = $r;
                }
                mysqli_free_result($resNL);
            }
        }

        mysqli_close($link);
        return array(
            'thongtin'   => $info,
            'sanpham'    => $dsSP,
            'nguyenlieu' => $dsNL
        );
    }
    
}
?>