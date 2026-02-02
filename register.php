<?php
session_start();
require_once 'config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email'])); 

    $password = $_POST['password'];

    $name = $conn->real_escape_string(trim($_POST['name']));
    $dob = $conn->real_escape_string($_POST['dob']);
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $address = $conn->real_escape_string(trim($_POST['address']));

    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = "Password must contain at least one Uppercase letter (A-Z).";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = "Password must contain at least one Number (0-9).";
    } elseif (!preg_match('/[\W]/', $password)) {
        $error = "Password must contain at least one Special Character (!@#$%^&*).";
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 

        $error = "Invalid Email Address format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } elseif ($dob > date('Y-m-d')) {
        $error = "Date of Birth cannot be in the future.";
    } else {

        $check_user = $conn->query("SELECT user_id FROM users WHERE username='$username'");

        $check_email = $conn->query("SELECT user_id FROM users WHERE email='$email'");

        if ($check_user->num_rows > 0) {
            $error = "❌ Username '$username' is already taken.";
        } elseif ($check_email->num_rows > 0) { 

            $error = "❌ This Email Address is already registered.";
        } else {

            $check_phone = $conn->query("SELECT patient_id FROM patients WHERE phone='$phone'");
            if ($check_phone->num_rows > 0) {
                $error = "❌ This Phone Number is already registered to a patient.";
            } else {

                $conn->begin_transaction();

                try {

                    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

                    $stmt1 = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'patient')");
                    $stmt1->bind_param("sss", $username, $hashed_pass, $email);
                    $stmt1->execute();

                    $new_user_id = $conn->insert_id;

                    $stmt2 = $conn->prepare("INSERT INTO patients (user_id, name, dob, phone, address, join_date) VALUES (?, ?, ?, ?, ?, CURDATE())");
                    $stmt2->bind_param("issss", $new_user_id, $name, $dob, $phone, $address);
                    $stmt2->execute();

                    $conn->commit();

                    header("Location: login.php");
                    exit();

                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "System Error: Registration failed. " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register - Patient Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="card shadow p-4 m-3" style="width: 100%; max-width: 450px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">New Patient</h3>
            <p class="text-muted small">Create your portal account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <h6 class="text-uppercase text-muted small fw-bold mb-3">Login Details</h6>

            <div class="form-floating mb-2">
                <input type="text" name="username" class="form-control" id="uName" placeholder="Username"
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    required>
                <label for="uName">Choose a Username</label>
            </div>

            <div class="form-floating mb-1">
                <input type="password" name="password" class="form-control" id="uPass" placeholder="Password" required>
                <label for="uPass">Choose a Password</label>
            </div>
            <div class="mb-4 text-muted" style="font-size: 0.75rem;">
                * Min 8 chars, 1 Uppercase, 1 Number, 1 Special Char
            </div>

            <hr>

            <h6 class="text-uppercase text-muted small fw-bold mb-3">Personal Details</h6>

            <div class="form-floating mb-2">
                <input type="text" name="name" class="form-control" id="fName" placeholder="Full Name"
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                <label for="fName">Full Name</label>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-6">
                    <div class="form-floating">
                        <input type="date" name="dob" class="form-control" id="dob"
                            value="<?php echo isset($_POST['dob']) ? $_POST['dob'] : ''; ?>"
                            max="<?php echo date('Y-m-d'); ?>" required>
                        <label for="dob">Date of Birth</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-floating">
                        <input type="tel" name="phone" class="form-control" id="phone" placeholder="Phone"
                            value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                            pattern="[0-9]{10}" maxlength="10" minlength="10"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        <label for="phone">Phone (10 Digits)</label>
                    </div>
                </div>
            </div>

            <div class="form-floating mb-2">
                <input type="email" name="email" class="form-control" id="uEmail" placeholder="Email Address"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                <label for="uEmail">Email Address</label>
            </div>

            <div class="form-floating mb-3">
                <textarea name="address" class="form-control" id="addr" placeholder="Address"
                    style="height: 80px;"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                <label for="addr">Address</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Register Account</button>
        </form>

        <div class="text-center mt-3">
            <span class="text-muted">Already registered?</span>
            <a href="login.php" class="text-decoration-none fw-bold">Login here</a>
        </div>

    </div>

</body>

</html>