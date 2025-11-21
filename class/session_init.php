<?php
// File khởi tạo session - PHẢI được include ở đầu tất cả các file
// TRƯỚC khi có bất kỳ output nào
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
?>
