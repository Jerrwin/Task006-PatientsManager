<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';

if (isset($_GET['id'])) {
    $visit_id = $_GET['id'];

    $sql = "DELETE FROM visits WHERE visit_id = '$visit_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['status'] = "✅ Visit record deleted successfully.";
    } else {
        $_SESSION['status'] = "❌ Error deleting record: " . $conn->error;
    }
} else {
    $_SESSION['status'] = "❌ No Visit ID provided.";
}

header("Location: list.php");
exit();
?>