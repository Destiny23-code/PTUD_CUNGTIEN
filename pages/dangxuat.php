<?php
// File: /pages/login/dangxuat.php

// 1. Bắt đầu phiên (Session)
// Cần thiết để có thể truy cập và hủy các biến $_SESSION
session_start();

// 2. Hủy tất cả các biến session đã đăng ký
// Lệnh này loại bỏ tất cả các biến trong mảng $_SESSION
$_SESSION = array();

// Hoặc sử dụng lệnh này (đảm bảo phiên được giải phóng)
// session_unset(); 

// 3. Hủy session trên server
// Lệnh này xóa file session trên server
session_destroy();

// 4. Chuyển hướng người dùng về trang đăng nhập hoặc trang chủ
// Thay đổi đường dẫn này cho phù hợp với cấu trúc file của bạn
// Ví dụ: Quay về trang đăng nhập
header("Location: ../pages/dangnhap.php"); 
exit;
?>