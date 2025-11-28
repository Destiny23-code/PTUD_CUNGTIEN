<?php
// bctk_hieusuat.php
$hieuSuatData = $model->getHieuSuatLaoDong();

$hsLabels = array();
$hsValues = array();
foreach ($hieuSuatData as $row) {
    // Tương thích PHP 5.2.6
    $hsLabels[] = 'T' . $row['Thang'];
    $hsValues[] = $row['HieuSuat'];
}
$hsValuesJson = json_encode($hsValues);
?>

<div class="card p-3 mb-4 shadow-sm">
    <h6 class="fw-bold">Hiệu suất lao động (%)</h6>
    <canvas id="hieuSuatChart"></canvas>
</div>

<script>
// SỬA: Dùng var thay cho const (PHP 5.2.6 compatible)
var ctxHS = document.getElementById('hieuSuatChart');
new Chart(ctxHS, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($hsLabels); ?>,
        datasets: [{
            label: 'Hiệu suất (%)',
            data: <?php echo $hsValuesJson; ?>,
            borderColor: '#198754',
            tension: 0.2,
            pointRadius: 5
        }]
    },
    options: { /* ... options Chart.js ... */ }
});
</script>