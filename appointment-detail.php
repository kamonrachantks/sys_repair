<?php
@session_start();

include 'class/class.scdb.php';

$query = new SCDB();


if ((!isset($_SESSION['user_repair'])) || ($_SESSION['user_repair'] == '')) {
    header("location: login.php");
    exit();
}

try {
    if (!$query->connect()) {
        throw new Exception("Database connection error: " . $query->getError());
    }

    $u_id = $_SESSION['p_id_repair'];
    $cid = isset($_GET['m_id']) ? $_GET['m_id'] : null;
    $sqlAppointmentHistory = "SELECT m.m_id, m.p_id, m.m_date_S, m.m_time, m.m_status, d.du_name, p.p_name, p.p_tel, m.m_date_C, s.s_price
        FROM tb_du_maint m
        JOIN tb_durable d ON m.du_id = d.du_id
        JOIN tb_hr_profile p ON m.p_id = p.p_id
        LEFT JOIN tb_du_maint_service s ON m.m_id = s.m_id
        WHERE m.p_id = :p_id";

    if (!empty($cid)) {
        $sqlAppointmentHistory .= " AND m.m_id = :m_id";
    }

    $stmtAppointmentHistory = $query->prepare($sqlAppointmentHistory);
    $stmtAppointmentHistory->bindParam(':p_id', $u_id, PDO::PARAM_INT);

    if (!empty($cid)) {
        $stmtAppointmentHistory->bindParam(':m_id', $cid, PDO::PARAM_INT);
    }

    $stmtAppointmentHistory->execute();
} catch (Exception $e) {
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
            margin: 0 auto;/* Adjust the maximum width as needed */
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
        <!-- Header Section -->
        <?php include_once('header.php'); ?>
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
                            <h4 style="padding-bottom: 20px; text-align: center; color: blue;">รายละเอียดการซ่อม</h4>
                            </div>
                            <table class="table table-bordered">
                                <?php while ($row = $stmtAppointmentHistory->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <tr>
                                        <th>หมายเลขแจ้งซ่อม</th>
                                        <td><?php echo $row['m_id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>ชื่อ</th>
                                        <td><?php echo $row['p_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>เบอร์โทร</th>
                                        <td><?php echo $row['p_tel']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>วันที่</th>
                                        <td><?php echo $row['m_date_S']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>เวลา</th>
                                        <td><?php echo date('h:i a', strtotime($row['m_time'])); ?></td>
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

                                    <tr>
                                        <th>Apply Date</th>
                                        <td><?php echo $row['m_date_C']; ?></td>
                                    </tr>

                                    <tr>
                                        <th>ค่าใช้ในการซ่อม</th>
                                        <td><?php echo $row['s_price']; ?></td>
                                    </tr>
                                                                      
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include_once('footer.php'); ?>

    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <script type="text/javascript" src="js/custom.js"></script>

</body>

</html>