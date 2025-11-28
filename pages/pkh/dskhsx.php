<?php
require_once("../../class/clskehoachsx.php");
$ctrl = new KeHoachModel();
$dsKeHoach = $ctrl->getDanhSachKeHoach(); // giả sử trả về array of assoc arrays
//include_once("../../layout/giaodien/bgd.php");
include_once("../../layout/giaodien/pkh.php");
?>

<!-- Bootstraps & jQuery (Bootstrap 3) -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<!-- (nếu layout đã load bootstrap ở bgd.php thì không cần tải lại) -->

<div class="content">
  <div class="container-fluid p-4 mt-3">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h4 class="panel-title text-primary"><i class="glyphicon glyphicon-list"></i> Danh sách kế hoạch sản xuất</h4>
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="bg-primary" style="color:#fff">
              <tr>
                <th style="width:40px">#</th>
                <th>Mã kế hoạch</th>
                <th>Mã đơn hàng</th>
                <th>Người lập</th>
                <th>Ngày lập</th>
                <th>Trạng thái</th>
                <th>Lý do từ chối</th>
                <th style="width:110px">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; if (is_array($dsKeHoach) && count($dsKeHoach)>0): ?>
                <?php foreach ($dsKeHoach as $k): ?>
                  <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars(isset($k['maKHSX']) ? $k['maKHSX'] : ''); ?></td>
                    <td><?php echo htmlspecialchars(isset($k['maDH']) ? $k['maDH'] : ''); ?></td>
                    <td><?php echo htmlspecialchars(isset($k['nguoiLap']) ? $k['nguoiLap'] : ''); ?></td>
                    <td><?php echo htmlspecialchars(isset($k['ngayLap']) ? $k['ngayLap'] : ''); ?></td>
                    <td>
                      <?php
                        $tr = isset($k['trangThai']) ? $k['trangThai'] : '';
                        $badge = 'label label-warning';
                        if ($tr == 'Đã duyệt' || $tr == 'Da duyet' ) $badge = 'label label-success';
                        elseif ($tr == 'Từ chối' || $tr == 'Tu choi') $badge = 'label label-danger';
                      ?>
                      <span class="<?php echo $badge; ?>"><?php echo htmlspecialchars($tr); ?></span>
                    </td>
                    <td><?php echo !empty($k['lyDoTuChoi']) ? htmlspecialchars($k['lyDoTuChoi']) : '-'; ?></td>
                    <td>
                      <button class="btn btn-xs btn-info" onclick="xemChiTiet('<?php echo addslashes($k['maKHSX']); ?>')">
                        <i class="glyphicon glyphicon-eye-open"></i> Xem
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="8" class="text-center">Không có kế hoạch nào.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal chi tiết -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:#f5f5f5; border-bottom:2px solid #337ab7;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title text-primary" id="detailModalLabel"><b>Chi tiết kế hoạch</b></h4>
      </div>
      <div class="modal-body" id="modalBody" style="font-size:14px;">
        <!-- content loaded by ajax -->
      </div>
      <div class="modal-footer text-center" style="border-top:1px solid #ddd;">
        <button type="button" class="btn btn-success" onclick="pheDuyet('duyet')"><i class="glyphicon glyphicon-ok"></i> Duyệt</button>
        <button type="button" class="btn btn-danger" onclick="hienLyDo()"><i class="glyphicon glyphicon-remove"></i> Từ chối</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal nhập lý do -->
<div class="modal fade" id="reasonModal" tabindex="-1" role="dialog" aria-labelledby="reasonModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:#d9534f;color:#fff;">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Nhập lý do từ chối</h4>
      </div>
      <div class="modal-body">
        <textarea id="lyDo" class="form-control" rows="4" placeholder="Nhập lý do..."></textarea>
      </div>
      <div class="modal-footer text-center">
        <button type="button" class="btn btn-danger" onclick="pheDuyet('tuChoi')">Xác nhận</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
      </div>
    </div>
  </div>
</div>

<!-- Styles for modal content -->
<style>
  .section-header { 
    background:#f7f7f7; 
    border-left:4px solid #337ab7; 
    padding:8px 10px; 
    margin-top:15px; 
    font-weight:bold;
  }
  .info-table td { 
    padding:6px 10px; 
    border-bottom:1px solid #eee; 
  }
  .detail-table { 
    width:100%; 
    border-collapse:collapse; 
    margin-top:8px; 
  }
  .detail-table th, 
  .detail-table td { 
    border:1px solid #ddd; 
    padding:6px 8px; 
  }
  /* ✅ Tiêu đề bảng (Mã SP, Tên sản phẩm...) thành chữ đen */
  .detail-table th { 
    background:#f1f1f1; 
    color:#000; 
    font-weight:600; 
  }
  .text-success { color:#28a745; } 
  .text-danger { color:#d9534f; }
</style>


<!-- jQuery & Bootstrap JS (load once; nếu bgd.php đã load thì bỏ) -->
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<script>
var currentKHSX = null;

function xemChiTiet(maKHSX) {
  currentKHSX = maKHSX;
  $.ajax({
    url: 'index.php',
    type: 'GET',
    data: { action: 'xemChiTiet', maKHSX: maKHSX },
    dataType: 'json',
    success: function(data) {
      if (!data || !data.thongtin) {
        alert('Không có dữ liệu chi tiết!');
        return;
      }

      // Map data fields safely (fallback '')
      var info = data.thongtin || {};
      var ma = info.maKHSX || info.maKeHoach || '';
      var maDH = info.maDH || info.maDonHang || '';
      var nguoiLap = info.nguoiLap || info.nguoi_lap || '';
      var ngayLap = info.ngayLap || info.ngay_lap || '';
      var trangThai = info.trangThai || info.trang_thai || '';

      // Build HTML
      var html = '';
      html += '<div class="section-header">Thông tin kế hoạch</div>';
      html += '<table class="info-table" width="100%">';
      html += '<tr><td style="width:20%"><b>Mã kế hoạch:</b></td><td>' + escapeHtml(ma) + '</td></tr>';
      html += '<tr><td><b>Mã đơn hàng:</b></td><td>' + escapeHtml(maDH) + '</td></tr>';
      html += '<tr><td><b>Người lập:</b></td><td>' + escapeHtml(nguoiLap) + '</td></tr>';
      html += '<tr><td><b>Ngày lập:</b></td><td>' + escapeHtml(ngayLap) + '</td></tr>';
      html += '<tr><td><b>Trạng thái:</b></td><td>' + escapeHtml(trangThai) + '</td></tr>';
      html += '</table>';

      // Sản phẩm
      html += '<div class="section-header">Sản phẩm trong đơn hàng</div>';
      html += '<table class="detail-table"><thead><tr><th>Mã SP</th><th>Tên sản phẩm</th><th>Số lượng</th><th>Đơn vị tính</th></tr></thead><tbody>';
      var sp = data.sanpham || [];
      if (sp.length > 0) {
        for (var i = 0; i < sp.length; i++) {
          var row = sp[i] || {};
          var maSP = row.maSP || row.idSP || '';
          var tenSP = row.tenSP || row.tenSanPham || '';
          var soLuong = row.soLuong || row.so_luong || row.soLuongSP || '';
          var dv = row.donViTinh || row.don_vi || '';
          html += '<tr><td>' + escapeHtml(maSP) + '</td><td>' + escapeHtml(tenSP) + '</td><td>' + escapeHtml(soLuong) + '</td><td>' + escapeHtml(dv) + '</td></tr>';
        }
      } else {
        html += '<tr><td colspan="4" class="text-center">Không có sản phẩm</td></tr>';
      }
      html += '</tbody></table>';

      // Nguyên liệu
      html += '<div class="section-header">Nguyên liệu</div>';
      html += '<table class="detail-table"><thead><tr><th>Mã NL</th><th>Tên NL</th><th>SL 1 SP</th><th>Tổng cần</th><th>Tồn kho</th><th>Thiếu</th><th>Phương án xử lý</th></tr></thead><tbody>';
      var nl = data.nguyenlieu || [];
      if (nl.length > 0) {
        for (var j = 0; j < nl.length; j++) {
          var r = nl[j] || {};
          var maNL = r.maNL || r.idNL || '';
          var tenNL = r.tenNL || '';
          var sl1sp = (typeof r.soLuong1SP !== 'undefined') ? r.soLuong1SP : (r.sl1sp || '');
          var tongCan = r.tongSLCan || r.tongCan || '';
          var tonKho = (typeof r.slTonTaiKho !== 'undefined') ? r.slTonTaiKho : (r.tonKho || '');
          var thieu = (typeof r.slThieuHut !== 'undefined') ? parseFloat(r.slThieuHut) : (typeof r.thieu !== 'undefined' ? parseFloat(r.thieu) : 0);
          var phuongAn = r.phuongAnXuLy || r.phuongAn || '';

          var thieuHtml = (thieu && thieu > 0) ? '<span class="text-danger">' + thieu.toFixed(3) + '</span>' : '<span class="text-success">Đủ</span>';

          html += '<tr>'
               + '<td>' + escapeHtml(maNL) + '</td>'
               + '<td>' + escapeHtml(tenNL) + '</td>'
               + '<td>' + escapeHtml(sl1sp) + '</td>'
               + '<td>' + escapeHtml(tongCan) + '</td>'
               + '<td>' + escapeHtml(tonKho) + '</td>'
               + '<td>' + thieuHtml + '</td>'
               + '<td>' + escapeHtml(phuongAn) + '</td>'
               + '</tr>';
        }
      } else {
        html += '<tr><td colspan="7" class="text-center">Không có nguyên liệu</td></tr>';
      }
      html += '</tbody></table>';

      $('#modalBody').html(html);
      $('#detailModal').modal('show');
    },
    error: function(xhr, status, err) {
      var msg = 'Lỗi khi tải chi tiết kế hoạch';
      if (xhr && xhr.responseText) msg += ': ' + xhr.responseText;
      alert(msg);
    }
  });
}

// show reason modal
function hienLyDo(){
  $('#reasonModal').modal('show');
}

// phe duyet / tu choi
function pheDuyet(hanhDong) {
  var lyDo = '';
  if (hanhDong === 'tuChoi') {
    lyDo = $('#lyDo').val();
    if ($.trim(lyDo) === '') { alert('Vui lòng nhập lý do!'); return; }
  }

  $.ajax({
    url: 'index.php?action=pheDuyet',
    type: 'POST',
    dataType: 'json',
    data: { maKHSX: currentKHSX, hanhDong: hanhDong, lyDo: lyDo },
    success: function(res) {
      if (res && res.success) {
        alert('Cập nhật thành công!');
        location.reload();
      } else {
        alert('Cập nhật thất bại!');
      }
    },
    error: function() { alert('Không thể kết nối server'); }
  });
}

// small helper to escape HTML (prevent XSS)
function escapeHtml(str) {
  if (typeof str === 'undefined' || str === null) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');
}
</script>
