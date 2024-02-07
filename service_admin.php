<?php
@session_start();

include 'class/class.scdb.php';

$lineNotifyToken = "Y3zH1oQp4rVu0Wx4wINmhNzy5wCwpVwCv5Dp8kfVkkI"; 

$query = new SCDB();

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

    // Get search parameters if submitted
    $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
    $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

    $sqlAppointments = "SELECT m.m_id, d.du_name, m_comment, m.m_date_S, m.m_time, m.m_status FROM tb_du_maint m
                        JOIN tb_durable d ON m.du_id = d.du_id
                        WHERE m.m_status IS NULL";

    // Add search conditions for date range
    if (!empty($startDate)) {
        $sqlAppointments .= " AND m.m_date_S >= :startDate";
    }
    if (!empty($endDate)) {
        $sqlAppointments .= " AND m.m_date_S <= :endDate";
    }

    $stmtAppointmentHistory = $query->prepare($sqlAppointments);

    // Bind search parameters
    if (!empty($startDate)) {
        $stmtAppointmentHistory->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    }
    if (!empty($endDate)) {
        $stmtAppointmentHistory->bindParam(':endDate', $endDate, PDO::PARAM_STR);
    }

    $stmtAppointmentHistory->execute();

    $errorInfo = $stmtAppointmentHistory->errorInfo();
    if ($errorInfo[0] !== '00000') {
        die("Database error: " . $errorInfo[2]);
    }
} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage());
}

?>

<style>
    body {
        height: 100vh;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    .wrapper {
        flex: 1;
    }
</style>

<!DOCTYPE html>
<html lang="en">
<meta charset="utf-8">
<title>ระบบแจ้งซ่อมครุภัณฑ์ออนไลน์</title>

<body class="sub_page">
    <div class="wrapper">
        <div class="hero_area">
            <?php include_once('header_admin.php'); ?>
        </div>

        <section class="about_section">
            <div class="container">
                <div class="row">
                    <section class="w3l-contact-info-main col-md-12" id="contact">
                        <div class="contact-sec">
                            <div class="container">
                                <div>
                                    <div class="cont-details">
                                        <div class="table-content table-responsive cart-table-content m-t-30">
                                            <div style="padding-top: 30px;">
                                                <h4 style="padding-bottom: 20px;text-align: center;color: #5c6bc0 ;">รายการแจ้งซ่อมรอยืนยัน</h4>
                                                <div>
                                                    <!-- Add date search form -->
                                                    <form method="get">
                                                        <label for="startDate">ตั้งแต่วันที่:</label>
                                                        <input type="date" name="startDate" id="startDate" value="<?php echo $startDate; ?>">

                                                        <label for="endDate">ถึงวันที่:</label>
                                                        <input type="date" name="endDate" id="endDate" value="<?php echo $endDate; ?>">

                                                        <button type="submit" class="btn btn-primary">ค้นหา</button>
                                                    </form>

                                                    <table border="2" class="table">
                                                        <thead class="gray-bg">
                                                            <tr>
                                                                <th>ลำดับ</th>
                                                                <th>รหัสการแจ้งซ่อม</th>
                                                                <th>ชื่อครุภัณฑ์</th>
                                                                <th>อาการที่แจ้ง</th>
                                                                <th>วันที่แจ้งซ่อม</th>
                                                                <th>เวลาแจ้งซ่อม</th>
                                                                <th>สถานะการซ่อม</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $cnt = 1;
                                                            while ($rowAppointmentHistory = $stmtAppointmentHistory->fetch(PDO::FETCH_ASSOC)) {
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo $cnt; ?></td>
                                                                    <td><?php echo $rowAppointmentHistory['m_id']; ?></td>
                                                                    <td><?php echo $rowAppointmentHistory['du_name']; ?></td>
                                                                    <td><?php echo $rowAppointmentHistory['m_comment']; ?></td>
                                                                    <td><p><?php echo $rowAppointmentHistory['m_date_S']; ?></p></td>
                                                                    <td><?php echo $rowAppointmentHistory['m_time']; ?></td>
                                                                    <td><?php
                                                                        $status = $rowAppointmentHistory['m_status'];
                                                                        if ($status == '') {
                                                                            echo "รอยืนยัน";
                                                                        } elseif ($status == '2') {
                                                                            echo "กำลังดำเนินการ";
                                                                        } elseif ($status == '1') {
                                                                            echo "เรียบร้อยแล้ว";
                                                                        }
                                                                        ?></td>
                                                                    <td>
                                                                        <button class="btn btn-primary" onclick="confirmAction(<?php echo $rowAppointmentHistory['m_id']; ?>, '<?php echo $rowAppointmentHistory['du_name']; ?>')">ยืนยัน</button>
                                                                        <button class="btn btn-secondary" onclick="cancelAction(<?php echo $rowAppointmentHistory['m_id']; ?>)">ยกเลิก</button>
                                                                    </td>
                                                                </tr>
                                                            <?php
                                                                $cnt = $cnt + 1;
                                                            } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </section>

        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

        <script>
            function confirmAction(maintenanceId, du_name) {
                $.ajax({
                    type: "POST",
                    url: "update_status.php",
                    data: {
                        m_id: maintenanceId,
                        new_status: 2
                    },
                    success: function (response) {
                        sendLineNotify("รายการแจ้งซ่อมหมายเลข: " + maintenanceId + "\nสถานะ: กำลังดำเนินการ\nครุภัณฑ์: " + du_name);
                        showAlert("Success", "ยืนยันสำเร็จ!", "success").then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr, status, error) {
                        showAlert("Error", "ยืนยันไม่สำเร็จ", "error");
                    }
                });
            }

            function cancelAction(maintenanceId) {
                $.ajax({
                    type: "POST",
                    url: "cancel_request.php",
                    data: {
                        m_id: maintenanceId
                    },
                    success: function (response) {
                        showAlert("Success", "ยกเลิกสำเร็จ!", "success").then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr, status, error) {
                        showAlert("Error", "ยกเลิกไม่สำเร็จ", "error");
                    }
                });
            }

            function showAlert(title, text, icon) {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            }

            function sendLineNotify(message) {
                $.ajax({
                    type: "POST",
                    url: "send_line_notify.php",
                    data: {
                        token: "<?php echo $lineNotifyToken; ?>",
                        message: message
                    },
                    success: function (response) {
                        console.log("LINE Notify message sent successfully!");
                    },
                    error: function (xhr, status, error) {
                        console.error("Error sending LINE Notify message: " + error);
                    }
                });
            }
        </script>

        <script type="text/javascript" src="js/bootstrap.js"></script>
        <script type="text/javascript" src="js/custom.js"></script>
    </div>
</body>

<?php include_once('footer.php'); ?>
</html>
