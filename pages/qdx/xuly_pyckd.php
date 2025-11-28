<?php
// FILE NÀY KHÔNG CÓ HTML
header('Content-Type: application/json; charset=utf-8');
require_once('../../class/clsLapPYCKD.php'); 

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$response = array('success' => false, 'error' => null); 

$pyckd_model = new clsLapPYCKD();

if ($action === 'insert' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nguoiLap = isset($_POST['nguoiLap']) ? $_POST['nguoiLap'] : '';
        $maLo = isset($_POST['maLo']) ? $_POST['maLo'] : '';
        $ghiChu = isset($_POST['ghiChu']) ? $_POST['ghiChu'] : '';
        
        if (empty($nguoiLap) || empty($maLo)) {
            $response['error'] = 'Thiếu thông tin người lập hoặc mã lô.';
        } else {
            // Gọi hàm insert (lưu ý Class sẽ tự map ghiChu vào cột tieuChi của DB)
            $result = $pyckd_model->insertPhieuYeuCauKiemDinh($nguoiLap, $maLo, $ghiChu);
            
            if ($result) {
                $response['success'] = true;
            } else {
                $response['error'] = 'Lỗi Database: Không thể lập phiếu hoặc cập nhật lô.';
            }
        }
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }
} else {
    $response['error'] = 'Yêu cầu không hợp lệ.';
}

echo json_encode($response);
exit; 
?>