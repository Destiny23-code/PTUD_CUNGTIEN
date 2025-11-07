<?php
    class ketnoi{
        public function connect (){
            $host = "localhost";
            $user = "root";
            $pass = "";
            $dbname = "qlsx";

            $conn = new mysqli($host, $user, $pass, $dbname);
            $conn->set_charset("utf8");
            return $conn;
        }
        public function laydulieu($conn, $sql){
            $kq = $conn ->query($sql);
            $data = array();
            if($kq->num_rows>0){
                while($r = $kq->fetch_assoc()){
                    $data[] =$r;
                }
            } 
            return $data;
        }
        public function xuly($link, $sql) {
        if ($link->query($sql) === TRUE) {
            return TRUE;
        } else {
            // Có thể thêm logic ghi log lỗi tại đây
            return FALSE;
        }
    }
    }
?>