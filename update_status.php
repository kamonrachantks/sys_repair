<?php
@session_start();

include 'class/class.scdb.php';

$query = new SCDB();

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the maintenance ID and new status from the POST data
    $maintenanceId = $_POST['m_id'];
    $newStatus = $_POST['new_status'];

    try {
        if (!$query->connect()) {
            throw new Exception("Database connection error: " . $query->getError());
        }

        // Update the m_status in the database to the new status
        $sqlUpdateStatus = "UPDATE tb_du_maint SET m_status = :new_status WHERE m_id = :maintenance_id";
        $stmtUpdateStatus = $query->prepare($sqlUpdateStatus);
        $stmtUpdateStatus->bindParam(':new_status', $newStatus, PDO::PARAM_INT);
        $stmtUpdateStatus->bindParam(':maintenance_id', $maintenanceId, PDO::PARAM_INT);
        $stmtUpdateStatus->execute();

        // Respond to the client
        echo "Success";
    } catch (Exception $e) {
        // Log or handle the exception appropriately
        header("HTTP/1.1 500 Internal Server Error");
        echo "An error occurred: " . $e->getMessage();
    }
} else {
    // Respond with a 400 Bad Request if the request method is not POST
    header("HTTP/1.1 400 Bad Request");
    echo "Bad Request";
}
?>
