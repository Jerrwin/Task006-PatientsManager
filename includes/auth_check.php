<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $path_prefix = file_exists('login.php') ? '' : '../';
    header("Location: " . $path_prefix . "login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {

    $path_prefix = file_exists('patient_dashboard.php') ? '' : '../';
    header("Location: " . $path_prefix . "patient_dashboard.php");
    exit();
}
?>