<?php 
// BƯỚC 1: Lấy đường dẫn trang hiện tại (loại bỏ query string nếu có, vd: ?id=1)
$current_path = strtok($_SERVER["REQUEST_URI"], '?'); 

// BƯỚC 2: Định nghĩa đường dẫn gốc của dự án
$base_path = '/ptud_cungtien'; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    
    <style>
        /* CSS để tạo giao diện giống hình ảnh */
        body, html {
            height: 100%;
        }

        body {
            /* Nền xanh gradient như hình */
            background: #0052D4; /* Fallback */
            background: -webkit-linear-gradient(to right, #65C7F7, #4364F7, #0052D4); /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to right, #65C7F7, #4364F7, #0052D4); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
            
            /* Căn giữa form */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 400px; /* Giới hạn chiều rộng form */
            border-radius: 15px; /* Bo góc */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .login-card .card-body {
            padding: 2.5rem; /* Tăng khoảng cách lề */
        }

        .login-logo {
            font-size: 3rem; /* Kích thước logo */
            color: #0d6efd; /* Màu xanh logo */
        }
        
        /* Tùy chỉnh input */
        .form-control {
            background-color: #f8f9fa; /* Màu nền input */
            border: none; /* Bỏ viền */
            padding: 0.75rem 1rem;
            height: 50px; /* Tăng chiều cao */
        }

        /* Tùy chỉnh nút */
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            height: 50px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="<?php echo $base_path; ?>/layout/images/logo.png" alt="logo" style="width:30%">
            </div>
        