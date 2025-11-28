<?php
require_once("clsconnect.php");

class login extends ketnoi {

    public function mylogin($user, $pass) {
        $pass = md5($pass); // vẫn giữ md5 như code cũ của nhóm
        $link = $this->connect();

        // ĐÃ XÓA gioiTinh vì bảng nhanvien chưa có cột này
        $sql = "SELECT tk.iduser, tk.username, tk.password, tk.phanquyen, 
                       nv.maNV, nv.tenNV
                FROM taikhoan tk
                JOIN nhanvien nv ON nv.iduser = tk.iduser
                WHERE tk.username = ? AND tk.password = ?
                LIMIT 1";

        $stmt = $link->prepare($sql);
        if (!$stmt) {
            return 0; // lỗi prepare
        }

        $stmt->bind_param("ss", $user, $pass);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            // Lưu session
            $_SESSION['login']      = true;
            $_SESSION['id']         = $row['iduser'];
            $_SESSION['user']       = $row['username'];
            $_SESSION['pass']       = $row['password'];
            $_SESSION['hoTen']      = $row['tenNV'];
            $_SESSION['maNV']       = $row['maNV'];
            $_SESSION['phanquyen']  = $row['phanquyen'];

            // Nếu sau này thêm cột gioiTinh thì chỉ cần bỏ comment dòng dưới
            // $_SESSION['gioiTinh'] = $row['gioiTinh'] ?? '';

            return $row['phanquyen']; // trả về quyền để điều hướng
        } else {
            return 0; // sai tài khoản/mật khẩu
        }
    }

    // Kiểm tra session còn hợp lệ không
    public function confirmlogin($id, $user, $pass, $phanquyen) {
        $link = $this->connect();
        $sql = "SELECT iduser FROM taikhoan 
                WHERE iduser = ? AND username = ? AND password = ? AND phanquyen = ? 
                LIMIT 1";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("issi", $id, $user, $pass, $phanquyen);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows == 1;
    }

    // Kiểm tra quyền truy cập trang
    public function checkPagePermission($requiredPermission) {
        if (!isset($_SESSION['phanquyen']) || $_SESSION['phanquyen'] != $requiredPermission) {
            echo "<script>
                    alert('Bạn không có quyền truy cập chức năng này!');
                    window.history.back();
                  </script>";
            exit();
        }
        return true;
    }
}
?>