<?php
// Include necessary files and start the session
@session_start();
include 'class/class.scdb.php';
$query = new SCDB();
$mode = isset( $_GET['Action']) ?  $_GET['Action'] : '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input from the form
    $p_cid = isset($_POST['p_cid']) ? $_POST['p_cid'] : '';
    $p_tel = isset($_POST['p_tel']) ? $_POST['p_tel'] : '';

    // Validate input (add more validation as needed)
    if (empty($p_cid) || empty($p_tel)) {
        $response = array('success' => false, 'message' => 'กรุณากรอกข้อมูลทุกช่อง');
    } else {
        // Check if the provided p_cid and p_tel match an entry in tb_hr_profile
        $profileParams = array($p_cid, $p_tel);
        $profileResult = $query->execute("SELECT p_id FROM tb_hr_profile WHERE p_cid = ? AND p_tel = ?", $profileParams);

        // Fetch the result as an associative array
        $profileData = $profileResult->fetch(PDO::FETCH_ASSOC);

        // Check if a matching profile is found
        if ($profileData) {
            // If the profile is found, redirect to the page for creating a new password
            $_SESSION['p_cid'] = $p_cid;
            $_SESSION['p_tel'] = $p_tel;

            $response = array('success' => true, 'redirect' => 'create_new_password.php');
        } else {
            $response = array('success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง');
        }
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>ลืมรหัสผ่าน</title>
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
                <h1 class="mb- text-center">ลืมรหัสผ่าน</h1>
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
                                                    <input class="form-control" type="text" name="p_cid" placeholder="เลขบัตรประชาชน" aria-describedby="basic-addon1" id="p_cid" required="true">
                                                    <label>เลขบัตรประชาชน</label>
                                                </div>

                                                <div class="form-floating mb-3 ">
                                                    <span class="input-group-addon" id="basic-addon1"></i></span>
                                                    <input class="form-control"  type="text" name="p_tel" placeholder="เบอร์โทรศัพท์" aria-describedby="basic-addon1" id="p_tel" required="true">
                                                    <label>เบอร์โทรศัพท์</label>
                                                </div>

                                                    <button type="submit" class="btn btn-primary"><i ></i> ตรวจสอบข้อมูล</button>

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
        $('.js-tilt').tilt({
            scale: 1.1
        });
    </script>

    <script src="js/main.js"></script>

    <script>
 $(document).ready(function () {
    $('#loginForm').submit(function (e) {
        e.preventDefault();

        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    // Show success message and redirect after a delay
                    Swal.fire({
                        title: 'ข้อมูลถูกต้อง',
                        text: 'กำลังเปลี่ยนเส้นทาง...',
                        icon: 'success',
                        timer: 1000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        title: 'ข้อมูลไม่ถูกต้อง',
                        text: response.message,
                        icon: 'error',
                        showConfirmButton: true
                    });
                }
            },
            error: function () {
                // Handle AJAX errors
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด',
                    text: 'มีบางอย่างผิดพลาดในการตรวจสอบข้อมูล',
                    icon: 'error',
                    showConfirmButton: true
                });
            }
        });
    });
});

    </script>

</div>
</body>



</html>