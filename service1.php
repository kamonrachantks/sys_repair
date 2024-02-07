<?php
@session_start();

include 'class/class.scdb.php';

$query = new SCDB();

// Redirect to login page if session variables are not set
if ((!isset($_SESSION['user_repair'])) || ($_SESSION['user_repair'] == '')) {
    header("location: login.php");
    exit();
}

try {
    if (!$query->connect()) {
        throw new Exception("Database connection error: " . $query->getError());
    }

// Fetch data for appointment history
$u_id = $_SESSION['p_id_repair'];

$sqlAppointmentHistory = "SELECT m.m_id, m.m_date_S, m.m_time, m.m_status, d.du_name FROM tb_du_maint m
    JOIN tb_durable d ON m.du_id = d.du_id
    WHERE m.p_id = :p_id";



// Check if start date is provided
if (isset($_GET['startDate']) && !empty($_GET['startDate'])) {
    $startDate = $_GET['startDate'];
    $sqlAppointmentHistory .= " AND m.m_date_S >= :startDate";
}

// Check if end date is provided
if (isset($_GET['endDate']) && !empty($_GET['endDate'])) {
    $endDate = $_GET['endDate'];
    $sqlAppointmentHistory .= " AND m.m_date_S <= :endDate";
}

$stmtAppointmentHistory = $query->prepare($sqlAppointmentHistory);
$stmtAppointmentHistory->bindParam(':p_id', $u_id, PDO::PARAM_INT);


// Bind start date parameter if set
if (isset($startDate)) {
    $stmtAppointmentHistory->bindParam(':startDate', $startDate, PDO::PARAM_STR);
}

// Bind end date parameter if set
if (isset($endDate)) {
    $stmtAppointmentHistory->bindParam(':endDate', $endDate, PDO::PARAM_STR);
}

$stmtAppointmentHistory->execute();



} catch (Exception $e) {
    // Log or handle the exception appropriately
    die("An error occurred: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
<title>ระบบแจ้งซ่อมครุภัณฑ์ออนไลน์</title>

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

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            background-color: #fff;
            border-collapse: collapse;
            border: 1px solid rgba(0, 0, 0, 0.125);
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
    </style>
</head>

<body class="sub_page">

    <div class="hero_area">

        <?php include_once('header.php'); ?>

    </div>

    <section class="service_section">
        <div class="container">
            <div class="row">

            <section class="w3l-contact-info-main col-md-12" id="contact">
                    <div class="contact-sec">
                        <div class="container">
                            <div>
                                <div class="cont-details">
                                    <div class="table-content table-responsive cart-table-content m-t-30">
                                    <div class="mb-5" style="padding-top: 30px;">
                                        <h4 style="padding-bottom: 20px;text-align: center;color: #5c6bc0 ;">รายการแจ้งซ่อม</h4>
                                        </div>
                                        <!-- Add this form section above your table -->
                                        <div style="padding-top: 10px;">
    <form method="get">

        <label for="startDate">ตั้งแต่วันที่:</label>
        <input type="date" name="startDate" id="startDate">

        <label for="endDate">ถึงวันที่:</label>
        <input type="date" name="endDate" id="endDate">

        <button type="submit" class="btn btn-primary">ค้นหา</button>
    </form>
</div>

                                       <div style="padding-top: 30px;">
                                        <table border="2" class="table">
                                            <thead class="gray-bg">
                                            <tr>
                                                    <th>ลำดับ</th>
                                                    <th>รหัสการแจ้งซ่อม</th>
                                                    <th>ชื่อครุภัณฑ์</th>
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
                                                        <td><p><?php echo $rowAppointmentHistory['m_date_S']; ?></p></td>
                                                        <td><?php echo $rowAppointmentHistory['m_time']; ?></td>
                                                        <td>
                                                            <?php
                                                           $status = $rowAppointmentHistory['m_status'];
                                                           if ($status == '') {
                                                               echo "รอยืนยัน";
                                                           } elseif ($status == '2') {
                                                               echo "กำลังดำเนินการ";
                                                           } elseif ($status == '1') {
                                                               echo "เรียบร้อยแล้ว";
                                                           }
                                                            ?></td>
                                                            <td><a href="appointment-detail.php?m_id=<?php echo $rowAppointmentHistory['m_id']; ?>" class="btn btn-primary">View</a></td>
                                                    </tr>
                                                <?php
                                                    $cnt = $cnt + 1;
                                                } ?>
                                                <?php
                                                if ($cnt === 1) { // No records found

                                                    echo '<tr><td colspan="8" style="text-align: center;">ไม่พบรายการที่ค้นหา</td></tr>';}
                                                    ?>
                                            </tbody>
                                        </table>
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



    <?php include_once('footer.php'); ?>

    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <script type="text/javascript" src="js/custom.js"></script>

</body>

</html>
