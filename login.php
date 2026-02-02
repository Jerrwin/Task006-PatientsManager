<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: index.php");
    } else {
        header("Location: patient_dashboard.php");
    }
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE username='$username'");

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];

            if ($user['role'] == 'patient') {

                $p_query = $conn->query("SELECT name FROM patients WHERE user_id = " . $user['user_id']);
                if ($p_row = $p_query->fetch_assoc()) {
                    $_SESSION['name'] = $p_row['name'];
                } else {
                    $_SESSION['name'] = "Patient"; 

                }
            } else {

                $_SESSION['name'] = "Administrator"; 
            }

            if ($user['role'] == 'admin') {
                header("Location: index.php");
            } else {
                header("Location: patient_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

<div class="card shadow p-4" style="width: 350px;">
    <h3 class="text-center mb-3">System Login</h3>

    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <div class="text-center mt-3">
        <a href="register.php">Register as New Patient</a>
    </div>
</div>

</body>
</html>