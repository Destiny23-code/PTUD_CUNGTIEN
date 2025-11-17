<?php include_once("../../layout/giaodien/congnhan.php"); 
include_once("../../class/clsCongNhan.php"); 
session_start();

$cn = new Congnhan();
$tt = $cn->getTTCN($_SESSION['maNV']); 


?>
<!-- ===== CONTENT ===== -->
  <div class="content">
    <h5 class="fw-bold text-primary mb-4">
      <i class="bi bi-calendar3 me-2"></i>Lịch làm việc của tôi
    </h5>

    <!-- Thông báo cố định -->
    <div class="alert alert-info">
      <i class="bi bi-info-circle me-2"></i>
      Lịch làm việc cố định từ <strong>Thứ 2 - Thứ 6</strong>, mỗi ngày có <strong>2 ca</strong>: Sáng <b>(7h-11h)</b> và Chiều <b>(13h-17h)</b>.
      Hệ thống chỉ hiển thị <strong>tăng ca</strong> hoặc <strong>nghỉ phép</strong> nếu có thay đổi.
    </div>

    <!-- Bảng lịch làm việc -->
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white fw-bold">
            <i class="bi bi-clock-history me-2"></i>Lịch làm việc tuần 20/10 - 24/10/2025
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover align-middle m-0 text-center">
                <thead> 
                    <tr>
                        <th style="width: 15%;">Ca làm</th>
                        <th style="width: 17%;">Thứ 2 (20/10)</th>
                        <th style="width: 17%;">Thứ 3 (21/10)</th>
                        <th style="width: 17%;">Thứ 4 (22/10)</th>
                        <th style="width: 17%;">Thứ 5 (23/10)</th>
                        <th style="width: 17%;">Thứ 6 (24/10)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold bg-light">
                            Ca sáng<br>
                            <small class="text-muted">(7:00 - 11:00)</small>
                        </td>
                        
                        <td><span class="badge bg-success">Bình thường</span></td>
                        
                        <td><span class="badge bg-success">Bình thường</span></td>
                        
                        <td>
                            <span class="badge bg-danger">Nghỉ phép</span>
                            <br><small class="text-muted">(Lý do cá nhân)</small>
                        </td>
                        
                        <td><span class="badge bg-success">Bình thường</span></td>
                        
                        <td><span class="badge bg-success">Bình thường</span></td>
                    </tr>
                    
                    <tr>
                        <td class="fw-bold bg-light">
                            Ca chiều<br>
                            <small class="text-muted">(13:00 - 17:00)</small>
                        </td>
                        
                        <td>
                            <span class="badge bg-warning text-dark">Tăng ca</span>
                            <br><small class="text-danger">Đến 18:30</small>
                        </td>
                        
                        <td><span class="badge bg-success">Bình thường</span></td>
                        
                        <td><span class="badge bg-success">Bình thường</span></td>
                        
                        <td><span class="badge bg-success">Bình thường</span></td>
                        
                        <td>
                            <span class="badge bg-warning text-dark">Tăng ca</span>
                            <br><small class="text-danger">Đến 19:30</small>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <td colspan="6" class="text-muted fw-semibold">Thứ 7 & Chủ nhật: Nghỉ theo lịch cố định</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php include_once ("../../layout/footer.php"); ?>