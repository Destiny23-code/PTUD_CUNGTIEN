<?php 
include_once("../../layout/giaodien/pkh.php"); 
include_once("../../class/clskehoachsx.php");

// 1️⃣ Lấy dữ liệu lọc từ GET
$maKH = isset($_GET['maKHSX']) ? trim($_GET['maKHSX']) : '';
$ngayLap = isset($_GET['ngayLap']) ? trim($_GET['ngayLap']) : '';
$trangThai = isset($_GET['trangThai']) ? trim($_GET['trangThai']) : '';

// 2️⃣ Gọi model để lấy danh sách kế hoạch
$kehoachModel = new KeHoachModel();
$data_kehoach = $kehoachModel->getDSKeHoach($maKH, $ngayLap, $trangThai);
?>

<div class="content">
  <h5 class="fw-bold text-primary mb-4">
    <i class="bi bi-calendar-check me-2"></i>Danh sách Kế hoạch Sản Xuất
  </h5>

  <!-- Bộ lọc nâng cao -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body bg-light rounded-3">
      <form method="GET" action="" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fw-semibold">🔍 Mã kế hoạch</label>
          <input type="text" name="maKHSX" class="form-control form-control-sm rounded-pill px-3"
                 placeholder="Nhập mã KH hoặc từ khóa..."
                 value="<?php echo htmlspecialchars($maKH); ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">📅 Ngày lập</label>
          <input type="date" name="ngayLap" class="form-control form-control-sm rounded-pill px-3"
                 value="<?php echo htmlspecialchars($ngayLap); ?>">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">📌 Trạng thái</label>
          <select name="trangThai" class="form-select form-select-sm rounded-pill px-3">
            <option value="">-- Tất cả --</option>
            <?php
            $trangThaiOptions = array('Hoàn thành','Đã duyệt','Đang thực hiện','Trễ hạn','Từ chối');
            foreach($trangThaiOptions as $option) {
                $selected = ($trangThai == $option) ? 'selected' : '';
                echo "<option value=\"{$option}\" {$selected}>{$option}</option>";
            }
            ?>
          </select>
        </div>

        <div class="col-md-3 text-center mt-2">
          <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill me-2 shadow-sm">
            <i class="bi bi-search me-1"></i> Tra cứu
          </button>
          <a href="<?php echo basename(__FILE__); ?>" 
             class="btn btn-outline-secondary btn-sm px-4 rounded-pill shadow-sm">
            <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Danh sách kế hoạch -->
  <div class="card shadow-sm">
    <div class="card-header bg-secondary text-white fw-bold">
      <i class="bi bi-list-ul me-2"></i>Danh sách kế hoạch
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered table-hover align-middle text-center mb-0 table-kh">
        <thead>
          <tr>
            <th>#</th>
            <th>Mã KH</th>
            <th>Ngày lập</th>
            <th>Ngày bắt đầu</th>
            <th>Ngày kết thúc</th>
            <th>Trạng thái</th>
            <th>Lý do từ chối</th>
            <th>Ghi chú</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (!empty($data_kehoach) && is_array($data_kehoach)) {
              $stt = 1;

              // Mảng badge trạng thái
             $badgeColors = array(
                'hoàn thành' => array('bg'=>'#d4edda','color'=>'#155724','icon'=>'check-circle','text'=>'Hoàn thành'),
                'đã duyệt' => array('bg'=>'#d4edda','color'=>'#155724','icon'=>'check-circle','text'=>'Đã duyệt'),
                'đang thực hiện' => array('bg'=>'#fff3cd','color'=>'#856404','icon'=>'hourglass-split','text'=>'Đang thực hiện'),
                'trễ hạn' => array('bg'=>'#f8d7da','color'=>'#721c24','icon'=>'exclamation-triangle','text'=>'Trễ hạn'),
                'từ chối' => array('bg'=>'#f8d7da','color'=>'#721c24','icon'=>'exclamation-triangle','text'=>'Từ chối')
            );


              foreach ($data_kehoach as $row) {
                $key = mb_strtolower(trim($row['trangThai']), 'UTF-8');
                $badge = "<span style='font-size:13px;font-weight:bold;padding:6px 10px;border-radius:20px;background-color:{$badgeColors[$key]['bg']};color:{$badgeColors[$key]['color']};'>
                            <i class='bi bi-{$badgeColors[$key]['icon']} me-1'></i>{$badgeColors[$key]['text']}
                        </span>";
                  echo '<tr style="cursor: pointer;" onclick="window.location=\'ctkhsx.php?xemchitiet=' . $row['maKHSX'] . '\'">';
                  echo "<td>{$stt}</td>";
                  echo "<td>" . htmlspecialchars($row['maKHSX']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['ngayLap']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['ngayBDDK']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['ngayKTDK']) . "</td>";
                  echo "<td>{$badge}</td>";
                  echo "<td>" . (!empty($row['lyDoTuChoi']) ? htmlspecialchars($row['lyDoTuChoi']) : '-') . "</td>";
                  echo "<td>" . (!empty($row['ghiChu']) ? htmlspecialchars($row['ghiChu']) : '-') . "</td>";
                  echo "</tr>";
                  $stt++;
              }
          } else {
              echo "<tr><td colspan='8' class='text-muted'>Không tìm thấy kế hoạch nào.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include_once("../../layout/footer.php"); ?>
