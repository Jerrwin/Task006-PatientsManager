<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $conn->real_escape_string($_POST['name']);
    $dob = $_POST['dob'];
    $join_date = $_POST['join_date'];
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);

    $today = date('Y-m-d');
    if ($dob > $today) {
        $_SESSION['status'] = "❌ Error: Date of Birth cannot be in the future!";
        header("Location: list.php");
        exit();
    }

    $sql = "INSERT INTO patients (name, dob, join_date, phone, address) 
            VALUES ('$name', '$dob', '$join_date', '$phone', '$address')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['status'] = "✅ Patient added successfully!";
    } else {
        $_SESSION['status'] = "❌ Error: " . $conn->error;
    }

    header("Location: list.php");
    exit();
}
?>