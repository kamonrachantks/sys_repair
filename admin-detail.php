<?php
@session_start();

define('LINE_API', "https://notify-api.line.me/api/notify");

$token = "Y3zH1oQp4rVu0Wx4wINmhNzy5wCwpVwCv5Dp8kfVkkI"; // ใส่ Token ของ Line Notify ที่นี่

// ฟังก์ชันสำหรับส่งข้อความไปที่ Line Notify
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

// รวมคลาสที่ใช้งานในฐานข้อมูล
include 'class/class.scdb.php';

// สร้างอ็อบเจ็กต์ SCDB ใหม่
$query = new SCDB();

// กระโดดไปยังหน้า login ถ้าไม่ได้ตั้งค่าตัวแปรเซสชันหรือว่าเป็นค่าว่าง
if (!isset($_SESSION['user_repair']) || empty($_SESSION['user_repair'])) {
    header("location: login.php");
    exit();
}
     // Check if u_status is 0, then redirect to index.php
     if ($_SESSION['u_status'] == 0) {
        header("location: index.php");
        exit();
    }

try {
    // พยายามเชื่อมต่อฐานข้อมูล
    if (!$query->connect()) {
        throw new Exception("ข้อผิดพลาดในการเชื่อมต่อกับฐานข้อมูล: " . $query->getError());
    }

    // ดึงข้อมูลทั้งหมดของประวัติการซ่อมที่มี m_status = 2
    $cid = isset($_GET['m_id']) ? $_GET['m_id'] : null;
    $sqlAppointmentHistory = "SELECT m.m_id, m.p_id, m.m_date_S, m.m_time, m.du_id, m.m_status, d.du_name, p.p_name, p.p_tel, m.m_date_C, s.s_price
        FROM tb_du_maint m
        JOIN tb_durable d ON m.du_id = d.du_id
        JOIN tb_hr_profile p ON m.p_id = p.p_id
        LEFT JOIN tb_du_maint_service s ON m.m_id = s.m_id
        WHERE m.m_status = 2 AND m.m_id = :cid";

    // เตรียมและดำเนินการคำสั่ง SQL
    $stmtAppointmentHistory = $query->prepare($sqlAppointmentHistory);
    $stmtAppointmentHistory->bindValue(':cid', $cid, PDO::PARAM_INT);
    $stmtAppointmentHistory->execute();
} catch (Exception $e) {
    // บันทึกหรือจัดการข้อผิดพลาดตามความเหมาะสม
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
}

// ตรวจสอบว่าฟอร์มถูกส่งหรือไม่
$mode = isset($_GET['Action']) ? $_GET['Action'] : '';
//$mode = isset($_POST['s_date']) ? 'chk' : '';

if ($mode == "chk") {
    try {
        // เริ่มทำธุรกรรม
        $query->beginTransaction();

        // รับข้อมูลจากฟอร์ม
        $s_date = $_POST['s_date'];
        $s_servicename = $_POST['s_servicename'];
        $s_price = $_POST['s_price'];
        $m_id = $_POST['m_id'];
        $s_status = isset($_POST['s_status']) ? $_POST['s_status'] : 0; // ถ้าไม่มีการเลือกให้เป็น 0 (ค่าเริ่มต้น)

        // ดึง du_id จาก tb_du_maint ตาม m_id
        $sqlFetchDuId = "SELECT du_id, p_id FROM tb_du_maint WHERE m_id = :m_id";
        $stmtFetchDuId = $query->prepare($sqlFetchDuId);
        $stmtFetchDuId->bindValue(':m_id', $m_id, PDO::PARAM_INT);
        $stmtFetchDuId->execute();
        $duIdResult = $stmtFetchDuId->fetch(PDO::FETCH_ASSOC);

        if (!$duIdResult) {
            die("ข้อผิดพลาด: ไม่พบ du_id สำหรับ m_id $m_id");
        }

        $du_id = $duIdResult['du_id'];
        $p_id = $duIdResult['p_id'];

        $sqlFetchPName = "SELECT p_name FROM tb_hr_profile WHERE p_id = :p_id";
        $stmtFetchPName = $query->prepare($sqlFetchPName);
        $stmtFetchPName->bindValue(':p_id', $duIdResult['p_id'], PDO::PARAM_INT);
        $stmtFetchPName->execute();
        $pNameResult = $stmtFetchPName->fetch(PDO::FETCH_ASSOC);

        if (!$pNameResult) {
            die("ข้อผิดพลาด: ไม่พบ p_name สำหรับ p_id " . $duIdResult['p_id']);
        }

        $p_name = $pNameResult['p_name'];

        // เตรียมคำสั่ง SQL สำหรับ INSERT ข้อมูลลงใน tb_du_maint_service
        $sqlInsertService = "INSERT INTO tb_du_maint_service (s_date, s_servicename, s_price, m_id, s_status, du_id) 
                            VALUES (:s_date, :s_servicename, :s_price, :m_id, :s_status, :du_id)";
        $stmtInsertService = $query->prepare($sqlInsertService);

        // ผูกพารามิเตอร์และดำเนินการคำสั่ง SQL
        $stmtInsertService->bindValue(':s_date', $s_date, PDO::PARAM_STR);
        $stmtInsertService->bindValue(':s_servicename', $s_servicename, PDO::PARAM_STR);
        $stmtInsertService->bindValue(':s_price', $s_price, PDO::PARAM_STR);
        $stmtInsertService->bindValue(':m_id', $m_id, PDO::PARAM_INT);
        $stmtInsertService->bindValue(':s_status', $s_status, PDO::PARAM_INT);
        $stmtInsertService->bindValue(':du_id', $du_id, PDO::PARAM_INT);

        try {
            if (!$stmtInsertService->execute()) {
                throw new Exception("Error inserting data into tb_du_maint_service: " . $query->getError());
            }
            // เตรียมคำสั่ง SQL สำหรับ UPDATE ข้อมูลใน tb_du_maint
            $m_date_C = date('Y-m-d'); // ถ้าคุณต้องการแทรกวันที่ปัจจุบัน
            $sqlInsertMaint = "UPDATE tb_du_maint SET m_date_C = :m_date_C, m_status = 1 WHERE m_id = :m_id";
            $stmtInsertMaint = $query->prepare($sqlInsertMaint);

            // ผูกพารามิเตอร์และดำเนินการคำสั่ง SQL
            $stmtInsertMaint->bindValue(':m_date_C', $m_date_C, PDO::PARAM_STR);
            $stmtInsertMaint->bindValue(':m_id', $m_id, PDO::PARAM_INT);
            $stmtInsertMaint->execute();

            $lineMessage = "เรียนคุณ $p_name \nแจ้งซ่อมหมายเลข: $m_id  \nสถานะการซ่อม: ซ่อมเรียบร้อย ";
            notify_message($lineMessage, $token);
            // Commit ธุรกรรมถ้าทุกอย่างเป็นที่เรียบร้อย
            $query->commit();

            // Send JSON response
            $response = array('success' => true);
            echo json_encode($response);
            exit();
        } catch (Exception $e) {
            // Return an error response
            $response = array('error' => false, 'message' => $e->getMessage());
            echo json_encode($response);
            exit();
        }
        } catch (Exception $e) {
            // Rollback ธุรกรรมในกรณีเกิดข้อผิดพลาด
            $query->rollBack();
            die("เกิดข้อผิดพลาด: " . $e->getMessage());
        }
    }
    




?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>ระบบแจ้งซ่อมครุภัณฑ์ออนไลน์</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11">
    <!-- Head content remains unchanged -->

    <!-- Custom CSS for Form Styling -->
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

        /* Center-align the content */
        .cont-details {
            max-width: 800px;
            width: 100%;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }

        .table th {
            background-color: #d0d9ff;
        }

        .gray-bg {
            background-color: #f8f9fa;
        }

        /* Button Style */
        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 20px;
            padding: 8px 16px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        /* Update Status Box Styles */
        .update-status-box {
            margin-top: 20px;
            /* Adjust the margin as needed */
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
        }

        .status-select {
            width: 150px;
            border-radius: 5px;
            padding: 8px;
            margin-right: 10px;
        }

        .update-btn {
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            cursor: pointer;
        }

        /* Add margin at the bottom of the table */
        .table-container {
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="sub_page">

    <div class="hero_area">
        <!-- Header Section -->
        <?php include_once('header_admin.php'); ?>
        <!-- End Header Section -->
    </div>

    <!-- About Section -->
    <section class="w3l-contact-info-main" id="contact">
        <div class="contact-sec">
            <div class="container">
                <div>
                    <div class="cont-details">
                        <div class="table-content table-responsive cart-table-content m-t-30">
                            <div style="padding-top: 30px;">
                                <h4 style="padding-bottom: 20px; text-align: center; color: blue;">รายละเอียดการซ่อม
                                </h4>
                            </div>
                            <div class="table-container mx-auto">
                                <table class="table table-bordered">
                                    <?php while ($row = $stmtAppointmentHistory->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <tr>
                                            <th>หมายเลขแจ้งซ่อม</th>
                                            <td>
                                                <?php echo $row['m_id']; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>ชื่อ</th>
                                            <td>
                                                <?php echo $row['p_name']; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>เบอร์โทร</th>
                                            <td>
                                                <?php echo $row['p_tel']; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>วันที่</th>
                                            <td>
                                                <?php echo $row['m_date_S']; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>เวลา</th>
                                            <td>
                                                <?php echo date('h:i a', strtotime($row['m_time'])); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>รหัสครุภณฑ์</th>
                                            <td>
                                                <?php echo $row['du_id']; ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>สถานะ</th>
                                            <td>
                                                <?php
                                                if ($row['m_status'] == "") {
                                                    echo "รอยืนยัน";
                                                } elseif ($row['m_status'] == "2") {
                                                    echo "กำลังดำเนินการ";
                                                } elseif ($row['m_status'] == "1") {
                                                    echo "เรียบร้อยแล้ว";
                                                }
                                                ?>
                                            </td>
                                        </tr>

                                    <?php } ?>
                                </table>

                                <!-- Add the Update Status Box -->
                                <div class="update-status-box mx-auto">
                                    <h4>Update Status</h4>
                                    <form name="form1" method="post" action="?Action=chk" id="form1">

                                        <?php
                                        // Reset the pointer of $stmtAppointmentHistory to the beginning
                                        $stmtAppointmentHistory->execute();
                                        while ($row = $stmtAppointmentHistory->fetch(PDO::FETCH_ASSOC)) {
                                            ?>
                                            <input type="hidden" name="m_id" value="<?php echo $row['m_id']; ?>">
                                            <input type="hidden" name="du_id" value="<?php echo $row['du_id']; ?>">

                                        <?php } ?>

                                        <div class="mb-3">
                                            <div style="padding-top: 30px;">
                                            <label for="s_date" class="form-label">วันที่ซ่อม</label>
                                            <input type="date" class="form-control appointment_date" name="s_date" required="true"
                                            min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>


                                        <div class="mb-3">
                                            <div style="padding-top: 30px;">
                                                <label for="s_servicename" class="form-label">การซ่อม</label>
                                                <textarea class="form-control" id="s_servicename" name="s_servicename"
                                                    style="height: 100px"></textarea>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div style="padding-top: 30px;">
                                                <label for="s_price" class="form-label">ราคาการซ่อม</label>
                                                <textarea class="form-control" id="s_price" name="s_price"
                                                  oninput="validateNumericInput(this)" ></textarea>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div style="padding-top: 30px;">
                                                <input class="form-check-input" type="checkbox" name="s_status"
                                                    value="1" id="checkboxStatus" checked required="true">
                                                <label class="form-check-label" for="checkboxStatus"> ซ่อมเรียบร้อยแล้ว
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div style="padding-top: 30px;">
                                                <button type="submit" class="btn btn-primary float-end"
                                                    id="tb_du_maint">
                                                    <i class="fa fa-save"></i> บันทึก
                                                </button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End About Section -->

    <?php include_once('footer.php'); ?>

    <!-- jQuery and other scripts -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <script type="text/javascript" src="js/custom.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script>
        // Your existing JavaScript code

        $(document).ready(function () {
            // Existing code

            // AJAX request for form submission
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
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false

                            }).then((result) => {
                                // Redirect to service_admin2.php after success
                                // if (result.dismiss === Swal.DismissReason.timer) {
                                window.location.href = 'service_admin2.php';
                                //}
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

    function validateNumericInput(element) {
    // Remove non-numeric characters
    element.value = element.value.replace(/[^0-9.]/g, '');

    // Ensure the value starts with a number or decimal point
    if (!/^[0-9.]/.test(element.value)) {
        element.value = '';
    }

    // Ensure only one decimal point is present
    var countDecimalPoints = (element.value.match(/\./g) || []).length;
    if (countDecimalPoints > 1) {
        element.value = element.value.slice(0, -1);
    }
}



    </script>


</body>

</html>