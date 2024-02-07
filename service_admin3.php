<?php
@session_start();

include 'class/class.scdb.php';

$query = new SCDB();

define('LINE_API', "https://notify-api.line.me/api/notify");

$token = "Y3zH1oQp4rVu0Wx4wINmhNzy5wCwpVwCv5Dp8kfVkkI"; // Replace with your actual LINE Notify token

if ((!isset($_SESSION['user_repair'])) || ($_SESSION['user_repair'] == '')) {
    header("location: login.php");
    exit();
}
     // Check if u_status is 0, then redirect to index.php
     if ($_SESSION['u_status'] == 0) {
        header("location: index.php");
        exit();
    }

try {
    if (!$query->connect()) {
        throw new Exception("Database connection error: " . $query->getError());
    }

    $optionsForDatalist = "";
    $sqlForDatalist = "SELECT * FROM tb_durable";
    $stmtForDatalist = $query->prepare($sqlForDatalist);
    $stmtForDatalist->execute();

    if ($stmtForDatalist) {
        while ($rowForDatalist = $stmtForDatalist->fetch(PDO::FETCH_ASSOC)) {
            $optionsForDatalist .= "<option value='{$rowForDatalist['du_id']}'>{$rowForDatalist['du_name']}</option>";
        }
    }
} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage());
}

$duIdNotFound = false;

$mode = isset( $_GET['Action']) ?  $_GET['Action'] : '';

if ($mode == "chk") {
    $du_id = $_POST['du_id'];
    $m_date_S = date('Y-m-d');
    $m_time = date('H:i:s');
    $m_comment = $_POST['m_comment'];
    $u_id = $_SESSION['p_id_repair'];

    // Check if $du_id exists in the provided data
    if (!duIdExists($du_id, $optionsForDatalist)) {
        $duIdNotFound = true;
        $response = array('success' => false, 'message' => 'รหัสครุภณฑ์ไม่ถูกต้อง');
        echo json_encode($response);
        exit();
    } else {
        // Use prepared statements consistently
        $sqlInsert = "INSERT INTO tb_du_maint (p_id, du_id, m_date_S, m_time, m_comment) VALUES (:p_id, :du_id, :m_date_S, :m_time, :m_comment)";
        $stmtInsert = $query->prepare($sqlInsert);

        $stmtInsert->bindValue(':p_id', $u_id, PDO::PARAM_INT);
        $stmtInsert->bindValue(':du_id', $du_id, PDO::PARAM_INT);
        $stmtInsert->bindValue(':m_date_S', $m_date_S, PDO::PARAM_STR);
        $stmtInsert->bindValue(':m_time', $m_time, PDO::PARAM_STR);
        $stmtInsert->bindValue(':m_comment', $m_comment, PDO::PARAM_STR);

        try {
            if (!$stmtInsert->execute()) {
                throw new Exception("Error inserting data into tb_du_maint: " . $query->getError());
            }

            // Fetch user name from tb_hr_profile
            $sqlUserProfile = "SELECT p_name FROM tb_hr_profile WHERE p_id = :u_id";
            $stmtUserProfile = $query->prepare($sqlUserProfile);
            $stmtUserProfile->bindValue(':u_id', $u_id, PDO::PARAM_INT);
            $stmtUserProfile->execute();
            $userProfile = $stmtUserProfile->fetch(PDO::FETCH_ASSOC);
            $p_name = $userProfile ? $userProfile['p_name'] : 'N/A';

            // Fetch durable name from tb_durable
            $sqlDurable = "SELECT du_name FROM tb_durable WHERE du_id = :du_id";
            $stmtDurable = $query->prepare($sqlDurable);
            $stmtDurable->bindValue(':du_id', $du_id, PDO::PARAM_INT);
            $stmtDurable->execute();
            $durable = $stmtDurable->fetch(PDO::FETCH_ASSOC);
            $du_name = $durable ? $durable['du_name'] : 'N/A';

            // Build a message with the inserted data
            $message = "รายการแจ้งซ่อมใหม่\n";
            $message .= "ชื่อผู้ใช้: $p_name\n";
            $message .= "ชื่อครุภณฑ์: $du_name\n";
            $message .= "วันที่แจ้งซ่อม: $m_date_S\n";
            $message .= "เวลาที่แจ้ง: $m_time\n";
            $message .= "รายละเอียดการซ่อม/ปัญหา: $m_comment";

            // Send notification to LINE Notify
            $res = notify_message($message, $token);

            // Return a success response
            $response = array('success' => true);
            echo json_encode($response);
            exit();
        } catch (Exception $e) {
            // Return an error response
            $response = array('success' => false, 'message' => $e->getMessage());
            echo json_encode($response);
            exit();
        }
    }
}

function notify_message($message, $token)
{
    $queryData = array('message' => $message);
    $queryData = http_build_query($queryData, '', '&');
    $headerOptions = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                "Authorization: Bearer " . $token . "\r\n" .
                "Content-Length: " . strlen($queryData) . "\r\n",
            'content' => $queryData
        ),
    );
    $context = stream_context_create($headerOptions);
    $result = file_get_contents(LINE_API, FALSE, $context);
    $res = json_decode($result);
    return $res;
}

function duIdExists($du_id, $optionsForDatalist)
{
    $pattern = "/<option value='" . preg_quote($du_id, '/') . "'>/";
    return preg_match($pattern, $optionsForDatalist);
}

$date=date('d-m');
$y=date('Y')+543;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>ระบบแจ้งซ่อมครุภัณฑ์ออนไลน์</title>
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

    <!-- SweetAlert2 Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">

    <style>
        .form-label {
            font-weight: bold;
        }

        .form-select,
        .form-control {
            border-radius: 20px;
            margin-bottom: 15px;
        }

        .blue-small-label {
            font-size: 12px;
            color: #1a237e;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .float-end {
            float: right;
        }
    </style>

</head>

<?php include_once('header_admin.php'); ?>

<body>

<!-- About Section -->
<section class="about_section">
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 mx-auto">
                            <div class="mb-5" style="margin-top: 50px;">
                                <h1>รายละเอียดการซ่อม</h1>
                            </div>

                            <form name="form1" method="post" action="?Action=chk" id="form1">
                                <div class="mb-3">
                                    <div style="padding-top: 30px;">
                                        <label for="du_id" class="form-label">รหัสครุภณฑ์</label>
                                        <input class="form-control" list="datalistOptions" id="du_id" name="du_id" required="true">
                                        <datalist id="datalistOptions">
                                            <?php echo $optionsForDatalist; ?>
                                        </datalist>
                                        <label for="floatingTextarea" class="blue-small-label">ค้นหารหัสครุภณฑ์</label>
                                    </div>
                                </div>

                                <?php
                                if ($duIdNotFound) {
                                    echo '<div class="alert alert-danger mt-3" role="alert"><strong></strong> รหัสครุภณฑ์ไม่ถูกต้อง</div>';
                                }
                                ?>

                                <div class="mb-3">
                                    <div style="padding-top: 30px;">
                                        <label>วันที่</label>
                                        <input type="text" class="form-control appointment_date" placeholder='<?php echo $date.'-'.$y;?>' name="m_date_S"  disabled>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div style="padding-top: 30px;">
                                        <label>เวลา</label>
                                        <input type="text" class="form-control appointment_time" placeholder='<?php echo date('H:i:s');?>' name="m_time"  disabled>
                                    </div>
                                </div>

                                <div class="mb-3">
    <div style="padding-top: 30px;">
        <label for="m_comment" class="form-label">รายละเอียดการซ่อม/ปัญหา</label>
        <input class="form-control" list="datalistOptions2" id="m_comment" name="m_comment" required="true">
        <datalist id="datalistOptions2">
            <?php
            try {
                // Prepare and execute query to fetch Details_repair from tb_du_Details_repair
                $sqlForDetailsRepair = "SELECT Details_repair FROM tb_du_Details_repair";
                $stmtForDetailsRepair = $query->prepare($sqlForDetailsRepair);
                $stmtForDetailsRepair->execute();

                // Loop through the results and generate options for datalist
                while ($rowForDetailsRepair = $stmtForDetailsRepair->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$rowForDetailsRepair['Details_repair']}'>{$rowForDetailsRepair['Details_repair']}</option>";
                }
            } catch (Exception $e) {
                // Handle any errors that occur during the query execution
                echo "An error occurred while fetching data: " . $e->getMessage();
            }
            ?>
        </datalist>
    </div>
</div>

                                <button type="submit" class="btn btn-primary float-end" id="tb_du_maint">
                                    <i class="fa fa-save"></i> บันทึก
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
        <!-- End About Section -->
        
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- SweetAlert2 Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script>
 $(document).ready(function () {
    // Form submission using AJAX
    $("#form1").submit(function (e) {
        e.preventDefault();

        // Serialize form data
        var formData = $(this).serialize();

        // AJAX request
        $.ajax({
            type: "POST",
            url: "?Action=chk",
            data: formData,
            success: function (response) {
                // Parse the JSON response
                var result = JSON.parse(response);

                // Check if the operation was successful
                if (result.success) {
                    // Show success message using SweetAlert2
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกรายการสำเร็จ',
                        timer: 1000,
                        timerProgressBar: true,
                        showConfirmButton: false
                            }).then(() => {
                                window.location.href = 'service_admin4.php';
                            });
                } else {
                    // Show error message using SweetAlert2
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: result.message
                    });
                }
            },
            error: function () {
                // Show generic error message using SweetAlert2
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'เกิดข้อผิดพลาดในการส่งข้อมูล'
                });
            }
        });
    });
});


    </script>

</body>

<?php include_once('footer.php'); ?>
</html>
