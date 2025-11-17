<?php 
include_once('../../layout/giaodien/pkh.php'); 
include_once('../../class/clskehoachsx.php');
include_once('../../class/clsDonHang.php');

// 1. KHỞI TẠO VÀ LẤY DỮ LIỆU LỌC
// Chỉ cần lấy giá trị thô, Model (lớp KeHoachModel) sẽ tự làm sạch.

$maDH = isset($_GET['maDH']) ? $_GET['maDH'] : '';
$ngayDat = isset($_GET['ngayDat']) ? $_GET['ngayDat'] : '';
$trangThai = isset($_GET['trangThai']) ? $_GET['trangThai'] : '';


$kehoachModel = new KeHoachModel();
$data_donhang = $kehoachModel->getDSDonHang($maDH, $ngayDat, $trangThai);

$stt = 1;

$dh = new DonHang();
$sanpham_list = $dh->getDSSanPham();
$khachhangModel = new DonHang();

$lookup_phone = '';
$customer_lookup_data = null;
$is_lookup_action = false;
$lookup_status_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'lookup_customer') {
    $is_lookup_action = true;
    $lookup_phone = trim($_POST['lookup_phone']);
    
    if ($lookup_phone !== '') {
        $customer_lookup_data = $khachhangModel->getKhachHangByPhone($lookup_phone);
        if ($customer_lookup_data) {
            $lookup_status_message = '<span class="text-success">✅ Khách hàng cũ.</span>';
        } else {
            $lookup_status_message = '<span class="text-danger">❌ Khách hàng chưa tồn tại.</span>';
        }
    } else {
        $lookup_status_message = '<span class="text-warning">⚠️ Vui lòng nhập số điện thoại.</span>';
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_order') {

    $sdt = trim($_POST['lookup_phone']);
    $tenKH = trim($_POST['customer_name']);
    $diaChi = trim($_POST['delivery_address']);
    $ngayGiao = trim($_POST['delivery_date']);
    $ghiChu = trim($_POST['order_note']);

    $products = $_POST['product_codes'];
    $quantities = $_POST['quantities'];

    $ngayDat = date('Y-m-d');
    $ngayGiaoMin = date('Y-m-d', strtotime("+7 days")); // tối thiểu 7 ngày

    // Kiểm tra ràng buộc
    if ($ngayGiao < $ngayGiaoMin) {
        echo "<script>alert('⚠️ Ngày giao dự kiến phải tối thiểu 7 ngày sau ngày đặt hàng ($ngayGiaoMin)');</script>";
    } elseif (empty($products) || empty($quantities) || count($products) != count($quantities)) {
        echo "<script>alert('⚠️ Vui lòng chọn ít nhất một sản phẩm và nhập số lượng hợp lệ');</script>";
    } else {
        // Kết nối 1 lần
        $conn = $dh->connect();

        // Kiểm tra khách hàng
        $khachHang = $dh->getKhachHangByPhone($sdt);
        if ($khachHang) {
            $maKH = $khachHang['maKH'];
        } else {
            $sqlKH = "INSERT INTO khachhang (tenKH, diaChi, sdt) 
                      VALUES ('{$tenKH}', '{$diaChi}', '{$sdt}')";
            if ($conn->query($sqlKH)) {
                $maKH = $conn->insert_id;
            } else {
                echo "<script>alert('❌ Lỗi khi tạo khách hàng');</script>";
                $conn->close();
                exit;
            }
        }

        // Thêm đơn hàng
        $sqlDH = "INSERT INTO donhang (maKH, ngayDat, ngayGiaoDuKien, trangThai, ghiChu) 
                  VALUES ('$maKH', '$ngayDat', '$ngayGiao', 'Mới tạo', '$ghiChu')";
        if ($conn->query($sqlDH)) {
            $maDH = $conn->insert_id;

            // Thêm chi tiết đơn hàng
            $allCTSuccess = true;
            foreach ($products as $index => $maSP) {
                $maSP = trim($maSP);
                $soLuong = intval($quantities[$index]);

                if ($maSP != '' && $soLuong > 0) { 
                    $sqlCT = "INSERT INTO chitiet_donhang (maDH, maSP, soLuong) 
                              VALUES ('$maDH', '$maSP', '$soLuong')";
                    if (!$conn->query($sqlCT)) {
                        $allCTSuccess = false;
                        break;
                    }
                } else {
                    $allCTSuccess = false;
                    break;
                }
            }

            if ($allCTSuccess) {
                echo "<script>alert('✅ Tạo đơn hàng thành công!');</script>";
            } else {
                echo "<script>alert('❌ Lỗi khi thêm chi tiết đơn hàng');</script>";
            }

        } else {
            echo "<script>alert('❌ Lỗi khi tạo đơn hàng');</script>";
        }

        $conn->close();
    }
}

?>




<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-primary">
            <i class="bi bi-card-list me-2"></i>Danh sách Đơn hàng
        </h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateOrder">
            <i class="bi bi-plus-circle me-1"></i> Tạo đơn hàng
        </button>
    </div>

    <div class="card p-3 mb-4 shadow-sm">
        <form class="row g-2 align-items-end" method="GET" action="">
            <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">Mã đơn hàng</label>
                <input type="text" class="form-control" placeholder="Nhập mã đơn..." name="maDH" 
                       value="<?php echo htmlspecialchars($maDH); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">Ngày đặt</label>
                <input type="date" class="form-control" name="ngayDat" 
                       value="<?php echo htmlspecialchars($ngayDat); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label mb-1 fw-semibold">Trạng thái</label>
                <select class="form-select" name="trangThai">
                    <option value="">Tất cả</option>
                    
                    <?php 
                    $status_options = array('Mới tạo', 'Đang sản xuất', 'Hoàn thành', 'Đã hủy');
                    // $current_status đã được đổi tên thành $trangThai
                    foreach ($status_options as $status) {
                        $selected = ($trangThai == $status) ? 'selected' : '';
                        echo "<option value=\"{$status}\" {$selected}>{$status}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3 d-flex justify-content-start align-items-end">
                <button type="submit" class="btn btn-primary me-2 w-30">
                    <i class="bi bi-search me-1"></i> Tìm
                </button>
                <button type="button" class="btn btn-outline-secondary w-40" onclick="window.location.href=window.location.pathname">
                    <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
                </button>
            </div>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white fw-bold">
            <i class="bi bi-list-ul me-2"></i>Danh sách Đơn hàng
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover align-middle m-0 text-center">
                <thead class="thead-blue ">
                    <tr>
                        <th>#</th>
                        <th style="width:10%">Mã DH</th>
                        <th style="width:15%">Ngày đặt</th>
                        <th style="width:15%">Ngày giao DK</th>
                        <th style="width:10%">Trạng thái</th>
                        <th style="width:520px">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Kiểm tra và lặp qua mảng dữ liệu trả về từ Model
                    if (is_array($data_donhang) && count($data_donhang) > 0) {
                        foreach ($data_donhang as $row) {
                            // Xử lý màu trạng thái (Giữ nguyên logic cũ)
                            $badgeClass = 'bg-secondary';
                            if ($row['trangThai'] == 'Hoàn thành') {
                                $badgeClass = 'bg-success';
                            } elseif ($row['trangThai'] == 'Đang sản xuất') {
                                $badgeClass = 'bg-warning text-dark';
                            } elseif ($row['trangThai'] == 'Mới tạo') {
                                $badgeClass = 'bg-info text-dark';
                            } elseif ($row['trangThai'] == 'Đã hủy') {
                                $badgeClass = 'bg-danger';
                            }
                            
                            // Sửa lỗi JavaScript (đã sửa ở lần trước)
                            echo '<tr style="cursor: pointer;" onclick="window.location=\'ctdh.php?xemchitiet=' . $row['maDH'] . '\'">';
                            echo "<td>" . $stt++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['maDH']). "</td>";
                            echo "<td>" . htmlspecialchars($row['ngayDat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ngayGiaoDuKien']) . "</td>";
                            echo "<td><span class='badge {$badgeClass}'>" . htmlspecialchars($row['trangThai']) . "</span></td>";
                            echo "<td class='text-start'>" . htmlspecialchars($row['ghiChu']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        // Hiển thị thông báo khi không có dữ liệu hoặc không tìm thấy
                        echo "<tr><td colspan='6' class='text-muted'>Không tìm thấy đơn hàng nào.</td></tr>";
                    }
                    
                    // KHÔNG CẦN $conn->close() Ở ĐÂY NỮA
                    // Model đã tự động đóng kết nối sau khi lấy dữ liệu.
                    ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Tạo Đơn Hàng -->
<div class="modal fade" id="modalCreateOrder" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-3">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-cart-plus me-2"></i> Tạo Đơn Hàng Mới
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-4">
        <form id="createOrderProForm" method="POST" novalidate>
          <!-- Thông tin khách hàng -->
          <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light fw-semibold">Thông tin khách hàng</div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label small fw-semibold">Số điện thoại</label>
                  <div class="input-group">
                    <input type="tel" class="form-control form-control-sm" 
                           id="customer_phone_lookup" name="lookup_phone" 
                           placeholder="0123456789"
                           value="<?php echo htmlspecialchars($lookup_phone); ?>">
                    <button type="submit" name="action" value="lookup_customer" formnovalidate
                            class="btn btn-sm btn-info text-white">
                      <i class="bi bi-search me-1"></i> Tra cứu
                    </button>
                  </div>
                  <small id="customer_lookup_status" class="form-text">
                    <?php
                      echo $is_lookup_action ? $lookup_status_message : '<span class="text-muted">Nhập số điện thoại và nhấn Tra cứu.</span>';
                    ?>
                  </small>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-semibold">Tên khách hàng</label>
                  <input type="text" class="form-control form-control-sm" name="customer_name"
                         placeholder="Nguyễn Văn A" 
                         value="<?php echo htmlspecialchars($customer_lookup_data['tenKH']); ?>">
                </div>
                <div class="col-12">
                  <label class="form-label small fw-semibold">Địa chỉ giao hàng</label>
                  <input type="text" class="form-control form-control-sm" name="delivery_address"  
                         placeholder="Nhập địa chỉ..." value="<?php echo htmlspecialchars($customer_lookup_data['diaChi']); ?>">
                </div>
              </div>
            </div>
          </div>

          <!-- Sản phẩm -->
          <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light fw-semibold d-flex justify-content-between align-items-center">
              Sản phẩm
              <button type="button" id="btnAddProductPro" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Thêm sản phẩm
              </button>
            </div>
            <div class="card-body p-3" id="productListPro">
              <div class="row g-2 mb-2 product-item-pro align-items-center">
                <div class="col-md-6">
                  <select class="form-select form-select-sm product-select" name="product_codes[]" required>
                    <option value="" disabled selected>-- Chọn sản phẩm --</option>
                    <?php foreach($sanpham_list as $sp): ?>
                        <option value="<?php echo htmlspecialchars($sp['maSP']); ?>">
                            <?php echo htmlspecialchars($sp['tenSP']); ?>
                        </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-2 ms-3">
                  <label class="form-label small fw-semibold">Số lượng: </label>
                </div>
                <div class="col-md-2">
                  <input type="number" class="form-control form-control-sm quantity-input" 
                         name="quantities[]" min="1" value="1" required>
                </div>
                <div class="col-md-1 text-center">
                  <button type="button" class="btn btn-outline-danger btn-sm remove-product-pro">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="card-footer text-end fw-bold">
              Tổng số lượng: <span id="totalAmountPro">0</span>
            </div>
          </div>

          <!-- Ghi chú & ngày giao -->
          <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light fw-semibold">Thông tin bổ sung</div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label small fw-semibold">Ngày giao dự kiến</label>
                  <input type="date" class="form-control form-control-sm" name="delivery_date" 
                         min="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                  <small class="text-muted">Ngày giao dự kiến phải cách ngày đặt ít nhất 7 ngày.</small>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-semibold">Ghi chú</label>
                  <textarea class="form-control form-control-sm" name="order_note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer form actions -->
          <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-outline-secondary btn-sm me-2" data-bs-dismiss="modal">
              <i class="bi bi-x-circle me-1"></i> Hủy
            </button>
            <button type="submit" name="action" value="create_order" class="btn btn-success btn-sm">
              <i class="bi bi-cart-check me-1"></i> Tạo đơn hàng
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include_once("../../layout/footer.php"); ?>
<!-- Sau footer (đảm bảo bootstrap js đã load), mở lại modal khi tra cứu -->
<?php
if ($is_lookup_action) {
  // Mở modal để hiển thị kết quả tra cứu
  echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      var m = document.getElementById('modalCreateOrder');
      if (m) {
        var modal = new bootstrap.Modal(m);
        modal.show();
        // Scroll tới input phone (tùy chọn)
        var phoneEl = document.getElementById('customer_phone_lookup');
        if (phoneEl) phoneEl.focus();
      }
    });
  </script>";
}
?>


<script>
document.addEventListener('DOMContentLoaded', function() {
  const productList = document.getElementById('productListPro');
  const btnAdd = document.getElementById('btnAddProductPro');
  const totalAmountEl = document.getElementById('totalAmountPro');

  function updateTotal() {
    let total = 0;
    productList.querySelectorAll('.quantity-input').forEach(input => {
      total += parseInt(input.value) || 0;
    });
    totalAmountEl.textContent = total;
  }

  // Thêm sản phẩm
  btnAdd.addEventListener('click', () => {
    const firstItem = productList.querySelector('.product-item-pro');
    const clone = firstItem.cloneNode(true);

    clone.querySelector('select').value = '';
    clone.querySelector('input').value = 1;

    productList.appendChild(clone);
    updateTotal();
  });

  // Xóa sản phẩm
  productList.addEventListener('click', function(e) {
    if (e.target.closest('.remove-product-pro')) {
      const items = productList.querySelectorAll('.product-item-pro');
      if (items.length > 1) {
        e.target.closest('.product-item-pro').remove();
        updateTotal();
      } else {
        alert('Phải có ít nhất một sản phẩm.');
      }
    }
  });

  // Cập nhật tổng khi số lượng thay đổi
  productList.addEventListener('input', function(e) {
    if (e.target.classList.contains('quantity-input')) {
      updateTotal();
    }
  });

  updateTotal();
});
</script>