<?php
@session_start();
include 'class/class.scdb.php';

$query = new SCDB();
$mode = isset( $_GET['Action']) ?  $_GET['Action'] : '';

if ($mode == "chklogin") {
    $user_no = $_POST['txtuser'] ?? '';
    $user_pw = $_POST['txtpass'] ?? '';

    $params = array($user_no);
    $result = $query->fetch("SELECT p_id, u_user, u_pass, u_status FROM tb_hr_user_io WHERE u_user = ?", $params);

    if (is_array($result) && count($result) > 0) {
        $hashed_password = $result['u_pass'];

        if (password_verify($user_pw, $hashed_password)) {
            $user = $result['u_user'];
            $p_id = $result['p_id'];
            $u_status = $result['u_status'];
            $_SESSION['user_repair'] = $user;
            $_SESSION['p_id_repair'] = $p_id;

            if ($u_status == 1) {
                $response = array('success' => true, 'redirect' => 'index_admin.php');
            } elseif ($u_status == 0) {
                $response = array('success' => true, 'redirect' => 'service.php');
            }
        } else {
            $response = array('success' => false, 'message' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง');
        }
    } else {
        $response = array('success' => false, 'message' => 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง');
    }

    // Send the JSON response to the AJAX request
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Makaan - Real Estate HTML Template</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Inter:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.20/dist/sweetalert2.all.min.js"></script>

</head>

<?php include_once('header.php');?>

<body>
<div class="wrapper">
    <div class="container-xxl bg-white p-0">
<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
              <div class="col-md-5 mx-auto">
                    <img class="img-fluid w-100" src="img/02.png">
                </div>
            <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                <h1 class="mb- text-center">Login</h1>
                <div class="form-signin w-100 m-auto">
                <form name="form1" id="loginForm" method="post" action="?Action=chklogin" onSubmit="return checkform(this);">
                    <div class="bgimg-1" id="fh5co-wrapper">
                        <div class="page-content--login">
                            <div id="fh5co-page">
                                <div class="">
                                    <div class="container" style="text-align:center;">
                                </div>
                                <div class="container">
                                    <div class="login-wrap">
                                        <div class="login-content">
                                            <div class="login-form" id="login-form">
                                                <div class="form-floating mb-3">
                                                    <span class="input-group-addon" id="basic-addon1"></i></span>
                                                    <input name="txtuser" type="text" class="form-control" placeholder="เลขทะเบียนเจ้าหน้าที่" aria-describedby="basic-addon1" id="txtuser" oninput=" this.value = this.value.replace(/(\..*)\./g, '$1');" onKeyDown="if(this.value.length==10 && event.keyCode!=11 && event.keyCode!=12) return false;" required="true" >
                                                    <label>เลขทะเบียนเจ้าหน้าที่</label>
                                                </div>

                                                <div class="form-floating mb-3 ">
                                                    <span class="input-group-addon" id="basic-addon1"></i></span>
                                                    <input name="txtpass" type="password" class="form-control" placeholder="รหัสผ่าน" aria-describedby="basic-addon1" id="txtpass" required="true">
                                                    <label>รหัสผ่าน</label>
                                                </div>

                                                <div class="center mb-3">
                                                    <button type="submit" class="btn btn-primary px-3 d-none d-lg-flex me-2" id="btnLogin"><i ></i> เข้าสู่ระบบ</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br><br><br><br>
                </form>
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script>
        $(document).ready(function() {
            $('#loginForm').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    type: $(this).attr('method'),
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'เข้าสู่ระบบสำเร็จ',
                                text: 'กำลังเปลี่ยนเส้นทาง...',
                                icon: 'success',
                                timer: 1000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = response.redirect;
                            });
                        } else {
                            Swal.fire({
                                title: 'เข้าสู่ระบบไม่สำเร็จ',
                                text: response.message,
                                icon: 'error',
                                showConfirmButton: true
                            });
                        }
                    }
                });
            });
        });
    </script>

</div>
</body>

<?php include_once('footer.php');?>

</html>