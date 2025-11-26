<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['hoTen'])) {
    header("Location: ../../pages/dangnhap/dangnhap.php");
    exit;
}

require_once("../../class/clsNhapKho.php");

$nk = new nhapkho();
$dsLo = $nk->layLoNhapDuoc();
?>

<?php include_once("../../layout/giaodien/khotp.php"); ?>

    <!-- Main Content -->
    <div class="content">
        <div class="card shadow-sm p-4">
            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-arrow-down-square me-2"></i>LẬP PHIẾU NHẬP KHO</h5>

            <form id="frmNhapKho" action="xuly_nhapkho.php" method="POST">
                <table class="table table-bordered align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th><input type="checkbox" id="chonTatCa"></th>
                            <th>Mã lô</th>
                            <th>Mã SP</th>
                            <th>Tên SP</th>
                            <th>Ngày SX</th>
                            <th>Số lượng</th>
                            <th>Trạng thái QC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dsLo as $row): ?>
                        <tr>
                            <td><input type="checkbox" class="chonLo" name="dsLo[]" value="<?php echo $row['maLo']; ?>">
                            </td>
                            <td><?php echo $row['maLo']; ?></td>
                            <td><?php echo $row['maSP']; ?></td>
                            <td><?php echo $row['tenSP']; ?></td>
                            <td><?php echo $row['ngaySX']; ?></td>
                            <td><?php echo $row['soLuong']; ?></td>
                            <td><?php echo $row['trangThaiQC']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Người lập:</label>
                        <input type="text" class="form-control"
                            value="<?php echo isset($_SESSION['hoTen']) ? $_SESSION['hoTen'] : ''; ?>" readonly>
                        <input type="hidden" id="nguoiLap" name="nguoiLap"
                            value="<?php echo isset($_SESSION['maNV']) ? $_SESSION['maNV'] : ''; ?>">
                        <?php 
                        if (!isset($_SESSION['maNV'])) {
                            echo '<div class="text-danger">Vui lòng đăng nhập lại để lấy thông tin người lập phiếu.</div>';
                        }
                        ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Ngày lập:</label>
                        <input type="date" class="form-control" id="ngayLapMain" value="<?php echo date("Y-m-d"); ?>">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="button" id="btnTaoPhieu" class="btn btn-primary">
                        <i class="bi bi-file-earmark-plus me-1"></i> Tạo phiếu nhập kho
                    </button>
                </div>

                <!-- PHIẾU CHI TIẾT (ẩn, hiện khi bấm tạo phiếu) -->
                <div id="phieuChiTiet" class="card mt-4 p-3 d-none">
                    <h5 class="fw-bold text-success">PHIẾU NHẬP KHO</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Mã phiếu</label>
                            <input type="text" id="maPhieu" name="maPhieu" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Người lập</label>
                            <input type="text" class="form-control"
                                value="<?php echo isset($_SESSION['hoTen'])?$_SESSION['hoTen']:'';?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ngày lập</label>
                            <input type="date" id="ngayLap" name="ngayLap" class="form-control" required>
                        </div>
                    </div>

                    <h6 class="fw-bold">Danh sách lô được nhập</h6>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Mã lô</th>
                                <th>Mã SP</th>
                                <th>Tên SP</th>
                                <th>Số lượng</th>
                            </tr>
                        </thead>
                        <tbody id="dsLoSelected"></tbody>
                    </table>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success" id="btnXacNhan">
                            <i class="bi bi-check-circle me-1"></i> Xác nhận nhập kho
                        </button>
                        <button type="button" class="btn btn-secondary ms-2" id="btnHuy">
                            Hủy
                        </button>
                    </div>
                </div>

                <!-- container for dynamically added hidden inputs dsLo[] -->
                <div id="hiddenInputs"></div>
            </form>

            </form>
        </div>
    </div>

    <script>
    document.getElementById('chonTatCa').addEventListener('change', function() {
        document.querySelectorAll('.chonLo').forEach(cb => cb.checked = this.checked);
    });

    // Helper to create PNK code
    function taoMaPhieu() {
        const ngay = new Date();
        const y = ngay.getFullYear();
        const m = String(ngay.getMonth() + 1).padStart(2, '0');
        const d = String(ngay.getDate()).padStart(2, '0');
        const rand = Math.floor(Math.random() * 900 + 100);
        return `PNK${y}${m}${d}-${rand}`;
    }

    // Khi bấm tạo phiếu: thu các lô được chọn, hiển thị chi tiết phiếu
    document.getElementById('btnTaoPhieu').addEventListener('click', function() {
        const rows = document.querySelectorAll('tbody tr');
        const selected = [];
        rows.forEach(row => {
            const cb = row.querySelector('.chonLo');
            if (cb && cb.checked) {
                const cells = row.querySelectorAll('td');
                selected.push({
                    maLo: cells[1].innerText.trim(),
                    maSP: cells[2].innerText.trim(),
                    tenSP: cells[3].innerText.trim(),
                    soLuong: cells[5].innerText.trim()
                });
            }
        });

        if (selected.length === 0) {
            alert('Vui lòng chọn ít nhất một lô để tạo phiếu.');
            return;
        }

        // Tạo mã phiếu và điền ngày
        document.getElementById('maPhieu').value = taoMaPhieu();
        const ngay = document.getElementById('ngayLapMain').value || new Date().toISOString().slice(0, 10);
        document.getElementById('ngayLap').value = ngay;

        // Điền bảng chi tiết
        const tbody = document.getElementById('dsLoSelected');
        tbody.innerHTML = '';
        const hiddenInputs = document.getElementById('hiddenInputs');
        hiddenInputs.innerHTML = '';
        selected.forEach(lo => {
            const tr = document.createElement('tr');
            tr.innerHTML =
                `<td>${lo.maLo}</td><td>${lo.maSP}</td><td>${lo.tenSP}</td><td>${lo.soLuong}</td>`;
            tbody.appendChild(tr);
            // tạo hidden input cho mỗi maLo
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'dsLo[]';
            inp.value = lo.maLo;
            hiddenInputs.appendChild(inp);
        });

        // Hiện phiếu chi tiết
        document.getElementById('phieuChiTiet').classList.remove('d-none');
        // cuộn tới phiếu chi tiết
        document.getElementById('phieuChiTiet').scrollIntoView({
            behavior: 'smooth'
        });
    });

    // Hủy phiếu chi tiết
    document.getElementById('btnHuy').addEventListener('click', function() {
        document.getElementById('phieuChiTiet').classList.add('d-none');
        document.getElementById('dsLoSelected').innerHTML = '';
        document.getElementById('hiddenInputs').innerHTML = '';
    });

    // Khi submit form, đảm bảo có maPhieu và dsLo
    document.getElementById('frmNhapKho').addEventListener('submit', function(e) {
        const ma = document.getElementById('maPhieu').value.trim();
        const hidden = document.querySelectorAll('input[name="dsLo[]"]');
        if (!ma || hidden.length === 0) {
            e.preventDefault();
            alert('Phiếu không hợp lệ. Vui lòng tạo phiếu lại.');
        }
    });
    </script>
    
<?php include_once("../../layout/footer.php"); ?>