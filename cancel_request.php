<?php
@session_start();
include 'class/class.scdb.php';

$query = new SCDB();

if (isset($_POST['m_id'])) {
    $m_id = $_POST['m_id'];

    try {
        if (!$query->connect()) {
            throw new Exception("Database connection error: " . $query->getError());
        }

        // Delete the record from tb_du_maint
        $sqlDeleteMaint = "DELETE FROM tb_du_maint WHERE m_id = :m_id";
        $stmtDeleteMaint = $query->prepare($sqlDeleteMaint);
        $stmtDeleteMaint->bindValue(':m_id', $m_id, PDO::PARAM_INT);
        $stmtDeleteMaint->execute();

        echo "success";
    } catch (Exception $e) {
       
        echo "error";
    }
} else {
    
    echo "error";
}
?>
