<?php
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql_visits = "DELETE FROM visits WHERE patient_id = '$id'";
    $conn->query($sql_visits);

    $sql_patient = "DELETE FROM patients WHERE patient_id = '$id'";

    if ($conn->query($sql_patient) === TRUE) {
        header("Location: list.php?msg=Patient and their records deleted successfully");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: list.php");
}
?>