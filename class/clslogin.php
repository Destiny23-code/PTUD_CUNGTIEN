<?php
    require_once("clsconnect.php");
    class login extends ketnoi{
       
        public function mylogin ($user, $pass){
            $pass = md5($pass);
            $link = $this->connect();
            $sql = "select tk.iduser, username, password, phanquyen, nv.tenNV, nv.maNV
             from taikhoan tk
            join nhanvien nv on nv.iduser = tk.iduser
            where tk.username = '$user' and tk.password = '$pass' limit 1";
            $kq = $link->query($sql);
            $i = $kq->num_rows;
            if ($i == 1){
                while($row = $kq->fetch_assoc()){
                $id = $row['iduser'];
                $username = $row['username'];
                $password = $row['password'];
                $tenNV = $row['tenNV'];
                $phanquyen = $row['phanquyen'];
                if (session_status() == PHP_SESSION_NONE) session_start();
                $_SESSION['login'] = true;
                $_SESSION['id'] = $id;
                $_SESSION['user'] = $username;
                $_SESSION['pass'] = $password;
                $_SESSION['hoTen'] = $tenNV;
                $_SESSION['phanquyen'] = $phanquyen;
                
                // Lấy mã nhân viên từ bảng nhanvien
                $sql_nv = "SELECT maNV FROM nhanvien WHERE iduser = '$id' LIMIT 1";
                $kq_nv = $link->query($sql_nv);
                if ($kq_nv && $row_nv = $kq_nv->fetch_assoc()) {
                    $_SESSION['maNV'] = $row_nv['maNV'];
                }
                
                return $phanquyen;
                //header("Location: index.php?act=admin"); exit();
                }
            }
            else{
                return 0;
            }
        }
        public function confirmlogin($id, $user, $pass, $phanquyen){
            $link = $this->connect();
            $sql = "select iduser from taikhoan where iduser='$id' and username='$user' and password='$pass' and phanquyen='$phanquyen' limit 1";
            $result = $link->query($sql);
            $num = $result->num_rows;
            /*if($num!=1){
                header("Location: view/login.php");
            }*/
            return $num == 1;
        }
    }
?>