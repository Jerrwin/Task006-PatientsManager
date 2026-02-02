<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $phone = $conn->real_escape_string(trim($_POST['phone']));

    $username = isset($_POST['username']) ? $conn->real_escape_string(trim($_POST['username'])) : $phone;
    $password = isset($_POST['password']) ? $_POST['password'] : $phone; 

    $email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : $phone . "@hospital.com";

    $name = $conn->real_escape_string(trim($_POST['name']));
    $dob = $_POST['dob'];
    $join_date = $_POST['join_date'];
    $address = $conn->real_escape_string(trim($_POST['address']));

    $today = date('Y-m-d');
    if ($dob > $today) {
        $_SESSION['status'] = "❌ Error: Date of Birth cannot be in the future!";
        header("Location: list.php");
        exit();
    }

    $check = $conn->query("SELECT user_id FROM users WHERE username='$username'");
    if ($check->num_rows > 0) {
        $_SESSION['status'] = "❌ Error: Username already exists!";
        header("Location: list.php");
        exit();
    }

    $conn->begin_transaction();

    try {

        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt1 = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'patient')");
        $stmt1->bind_param("sss", $username, $hashed_pass, $email);
        $stmt1->execute();

        $new_user_id = $conn->insert_id;

        $stmt2 = $conn->prepare("INSERT INTO patients (user_id, name, dob, phone, address, join_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("isssss", $new_user_id, $name, $dob, $phone, $address, $join_date);
        $stmt2->execute();

        $conn->commit();
        $_SESSION['status'] = "✅ Patient added successfully with Login!";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['status'] = "❌ System Error: " . $e->getMessage();
    }

    header("Location: list.php");
    exit();
}
?>