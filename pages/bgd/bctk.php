<?php
require_once("../../class/clskehoachsx.php");
include_once("../../layout/giaodien/bgd.php"); 

$reportType = isset($_GET['type']) ? $_GET['type'] : 'kehoachsanxuat'; 
$model = new KeHoachModel();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<div class="content">
    <div class="container-fluid p-4 mt-3">
        <h3 class="fw-bold text-primary"><i class="bi bi-bar-chart-fill me-2"></i>BÁO CÁO THỐNG KÊ</h3>
        
        <div class="mb-4">
            <label class="form-label fw-bold">Chọn loại báo cáo:</label>
            <select class="form-select w-25" onchange="window.location.href='bctk.php?type=' + this.value">
                <option value="kehoachsanxuat" <?php echo ($reportType == 'kehoachsanxuat') ? 'selected' : ''; ?>>Kế hoạch sản xuất</option>
                <option value="sanluong" <?php echo ($reportType == 'sanluong') ? 'selected' : ''; ?>>Sản lượng</option>
                <option value="chatluong" <?php echo ($reportType == 'chatluong') ? 'selected' : ''; ?>>Chất lượng sản phẩm</option>
            </select>
        </div>

        <div id="report-container">
            <?php 
            if ($reportType == 'kehoachsanxuat') {
                include('views/bctk_kehoach.php');
            } elseif ($reportType == 'sanluong') {
                include('views/bctk_sanluong.php');
            } elseif ($reportType == 'chatluong') {
                include('views/bctk_chatluong.php');
            }
            ?>
        </div>
    </div>
</div>

<?php include_once("../../layout/footer.php"); ?>