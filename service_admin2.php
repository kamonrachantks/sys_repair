<?php
@session_start();

include 'class/class.scdb.php';

$query = new SCDB();

// Redirect to login page if session variables are not set
if ((!isset($_SESSION['user_repair'])) || ($_SESSION['user_repair'] == '')) {
    header("location: login.php");
    exit();
}
     // Check if u_status is 0, then redirect to index.php
     if ($_SESSION['u_status'] == 0) {
        header("location: index.php");
        exit();
    }

// Set the number of records per page
$recordsPerPage = 30;

// Get the current page or set it to 1 if not set
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Get search parameters if submitted
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

// Calculate the starting record for the current page
$startFrom = ($page - 1) * $recordsPerPage;

try {
    if (!$query->connect()) {
        throw new Exception("Database connection error: " . $query->getError());
    }

    // Modify the SQL query to include the search condition and pagination
    $sqlAppointments = "SELECT m.m_id, d.du_name, m.m_date_S, m.m_time, m.m_status
                    FROM tb_du_maint m
                    JOIN tb_durable d ON m.du_id = d.du_id
                    WHERE m.m_status = 1";

    // Add search conditions
    if (!empty($searchTerm)) {
        $sqlAppointments .= " AND m.m_id = :searchTerm";
    }
    if (!empty($startDate)) {
        $sqlAppointments .= " AND m.m_date_S >= :startDate";
    }
    if (!empty($endDate)) {
        $sqlAppointments .= " AND m.m_date_S <= :endDate";
    }

    $sqlAppointments .= " ORDER BY m.m_id DESC
                    OFFSET $startFrom ROWS
                    FETCH NEXT $recordsPerPage ROWS ONLY";
    $stmtAppointmentHistory = $query->prepare($sqlAppointments);

    // Bind search parameters
    if (!empty($searchTerm)) {
        $stmtAppointmentHistory->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    }
    if (!empty($startDate)) {
        $stmtAppointmentHistory->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    }
    if (!empty($endDate)) {
        $stmtAppointmentHistory->bindParam(':endDate', $endDate, PDO::PARAM_STR);
    }

    $stmtAppointmentHistory->execute();

    // Get the total number of records for pagination
    $totalRecordsQuery = "SELECT COUNT(*) as total FROM tb_du_maint WHERE m_status = 1";

    // Add search conditions
    if (!empty($searchTerm)) {
        $totalRecordsQuery .= " AND m_id = :searchTerm";
    }
    if (!empty($startDate)) {
        $totalRecordsQuery .= " AND m_date_S >= :startDate";
    }
    if (!empty($endDate)) {
        $totalRecordsQuery .= " AND m_date_S <= :endDate";
    }

    $totalRecordsStmt = $query->prepare($totalRecordsQuery);

    // Bind search parameters
    if (!empty($searchTerm)) {
        $totalRecordsStmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    }
    if (!empty($startDate)) {
        $totalRecordsStmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    }
    if (!empty($endDate)) {
        $totalRecordsStmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
    }

    $totalRecordsStmt->execute();
    $totalRecords = $totalRecordsStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Calculate the total number of pages
    $totalPages = ceil($totalRecords / $recordsPerPage);

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

        <!-- About Section -->
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
                                                <h4 style="padding-bottom: 20px;text-align: center;color: #5c6bc0 ;">รายการแจ้งซ่อมที่เรียบร้อยแล้ว</h4>
                                                <div>
                                                    <div style="padding-top: 10px;">
                                                        <form method="get">

                                                            <label for="startDate">ตั้งแต่วันที่:</label>
                                                            <input type="date" name="startDate" id="startDate" value="<?php echo $startDate; ?>">

                                                            <label for="endDate">ถึงวันที่:</label>
                                                            <input type="date" name="endDate" id="endDate" value="<?php echo $endDate; ?>">

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
                                                                        <td><a href="admin-detail1.php?m_id=<?php echo $rowAppointmentHistory['m_id']; ?>" class="btn btn-primary">View</a></td>
                                                                    </tr>
                                                                <?php
                                                                    $cnt = $cnt + 1;
                                                                } ?>
                                                                <?php
                                                                if ($cnt === 1) { // No records found
                                                                    echo '<tr><td colspan="8" style="text-align: center;">ไม่พบรายการที่ค้นหา</td></tr>';
                                                                }
                                                                ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <!-- Display pagination links -->
                <div style="padding-top: 10px;">
                    <ul class="pagination">
                        <?php
                        if ($page > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1">First</a></li>';
                            $prevPage = $page - 1;
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $prevPage . '&search=' . $searchTerm . '&startDate=' . $startDate . '&endDate=' . $endDate . '">Previous</a></li>';
                        }

                        for ($i = 1; $i <= $totalPages; $i++) {
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '&search=' . $searchTerm . '&startDate=' . $startDate . '&endDate=' . $endDate . '">' . $i . '</a></li>';
                        }

                        if ($page < $totalPages) {
                            $nextPage = $page + 1;
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $nextPage . '&search=' . $searchTerm . '&startDate=' . $startDate . '&endDate=' . $endDate . '">Next</a></li>';
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '&search=' . $searchTerm . '&startDate=' . $startDate . '&endDate=' . $endDate . '">Last</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </section>
        <!-- End About Section -->

        <!-- JavaScript Dependencies -->
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script>
            function confirmAction(maintenanceId) {
                // Making an AJAX request to update m_status
                $.ajax({
                    type: "POST",
                    url: "update_status.php", // Replace with the actual file that handles the update
                    data: {
                        m_id: maintenanceId,
                        new_status: 2 // Set the new status value
                    },
                    success: function (response) {
                        // Handle the response if needed
                        alert("Status updated successfully!");
                    },
                    error: function (error) {
                        console.error("Error updating status: " + error);
                    }
                });
            }
        </script>
        <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.js"></script>
        <script type="text/javascript" src="js/custom.js"></script>
    </div>
</body>

<!-- Footer Section -->
<?php include_once('footer.php'); ?>

</html>
