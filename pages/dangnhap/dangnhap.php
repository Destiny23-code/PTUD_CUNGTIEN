<?php
session_start();
 include_once("../../layout/dangnhap/login.php")?>

<form method="POST">
    <div class="mb-3">
        <input type="text" class="form-control" name="username" placeholder="Tên đăng nhập" required>
    </div>

    <div class="mb-3">
        <input type="password" class="form-control" name="password" placeholder="Mật khẩu" required>
    </div>

    <input type="submit" name="btnLogin" class="btn btn-primary w-100 mt-3" value="Đăng nhập">

    <?php if(isset($_GET['error'])): 
        $message = ($_GET['error'] == 2) ? "Vui lòng nhập đầy đủ thông tin." : "Tên đăng nhập hoặc mật khẩu không chính xác!";
    ?>
    <div class="alert alert-danger mt-3" role="alert">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>
</form>

<?php
if(isset($_POST["btnLogin"])) {
    require_once("../../class/clslogin.php"); 
    $p = new login(); 
    $user = $_POST["username"];
    $pass = $_POST["password"];

    if($user != '' && $pass != ''){
        $phanquyen_result = $p->mylogin($user, $pass);
        
        if ($phanquyen_result && $phanquyen_result != 0) { // Đăng nhập thành công
            $redirect_url = "../trangchu/index.php";

            switch ($phanquyen_result) {
                case '1': $redirect_url = "../../layout/giaodien/pkh.php"; break;
                case '2': $redirect_url = "../../layout/giaodien/qdx.php"; break;
                case '3': $redirect_url = "../../layout/giaodien/khonl.php"; break;
                case '4': $redirect_url = "../../layout/giaodien/khotp.php"; break;
                case '5': $redirect_url = "../../layout/giaodien/qc.php"; break;
                case '6': $redirect_url = "../../layout/giaodien/bgd.php"; break;
                // Thêm các quyền khác tương tự ở đây
            }
            
            header("Location: " . $redirect_url);
            exit();

        } else {
            // Đăng nhập thất bại
            header("Location: dangnhap.php?error=1");
            exit();
        }
    } else {
        // Thiếu thông tin
        header("Location: dangnhap.php?error=2");
        exit();
    }
}
?>