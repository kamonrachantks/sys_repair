<?php
@session_start();

include 'class/class.scdb.php';

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

    $sqlTotalRequests = "SELECT COUNT(*) as total_requests FROM tb_du_maint WHERE m_status IS NULL";
    $sqlTotalRequests2 = "SELECT COUNT(*) as total_requests FROM tb_du_maint WHERE m_status = 2";
    $sqlTotalRequests1 = "SELECT COUNT(*) as total_requests FROM tb_du_maint WHERE m_status = 1";
    $stmtTotalRequests = $query->prepare($sqlTotalRequests);
    $stmtTotalRequests->execute();
    $totalRequests = $stmtTotalRequests->fetch(PDO::FETCH_ASSOC)['total_requests'];

    $stmtTotalRequests2 = $query->prepare($sqlTotalRequests2);
    $stmtTotalRequests2->execute();
    $totalRequests2 = $stmtTotalRequests2->fetch(PDO::FETCH_ASSOC)['total_requests'];

    $stmtTotalRequests1 = $query->prepare($sqlTotalRequests1);
    $stmtTotalRequests1->execute();
    $totalRequests1 = $stmtTotalRequests1->fetch(PDO::FETCH_ASSOC)['total_requests'];
} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
    <title>ระบบแจ้งซ่อมครุภัณฑ์ออนไลน์</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .dashboard-container {
            display: flex;
            justify-content: center; 
            flex-wrap: wrap; 
        }

        .dashboard-section {
            padding: 20px;
            border: 3px solid #28a745;
            border-radius: 10px;
            margin: 20px;
            max-width: 500px; 
            flex: 1; 
        }

        .total-requests {
            font-size: 19px;
            font-weight: bold;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
        }

        .green-button {
            background-color: #28a745;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            text-decoration: none;
            margin-top: 10px; 
            display: block; 
        }

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
</head>

<body class="sub_page">
<div class="wrapper">

    <div class="hero_area">
        <!-- Header Section -->
        <?php include_once('header_admin.php'); ?>
        <!-- End Header Section -->
    </div>

    <!-- Dashboard Sections -->
    <div class="dashboard-container text-center">

        <section class="dashboard-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h4>รายการแจ้งซ่อมรอยืนยัน</h4>
                        <img class="img" src="img/clockwise.png" style="width: 80px; height: 80px; margin: 0 auto;">
                        <a href="service_admin.php" class="total-requests green-button">
                            จำนวน: <?php echo $totalRequests; ?> รายการ
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h4>รายการแจ้งซ่อมกำลังดำเนินการ</h4>
                        <img class="img" src="img/schedule.png" style="width: 80px; height: 80px; margin: 0 auto;">
                        <a href="service_admin1.php" class="total-requests green-button">
                            จำนวน: <?php echo $totalRequests2; ?> รายการ
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-section text-center">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h4>รายการแจ้งซ่อมเรียบร้อยแล้ว</h4>
                        <img class="img" src="img/work.png" style="width: 80px; height: 80px; margin: 0 auto;">
                        <a href="service_admin2.php" class="total-requests green-button">
                            จำนวน: <?php echo $totalRequests1; ?> รายการ
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/custom.js"></script>
    </div>
</body>


    <?php include_once('footer.php'); ?>

</html>
