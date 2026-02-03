<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$error = "";

$id_to_edit = 0;

if ($role === 'admin') {

    require_once '../includes/header.php'; 

    if (isset($_GET['id'])) {
        $id_to_edit = $_GET['id'];
    } elseif (isset($_POST['patient_id'])) {
        $id_to_edit = $_POST['patient_id'];
    } else {
        echo "<script>window.location.href='list.php';</script>";
        exit();
    }

} else {

    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Profile</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <span class="navbar-brand mb-0 h1">My Profile</span>
            <a href="../patient_dashboard.php" class="btn btn-light btn-sm fw-bold">Back to Dashboard</a>
        </div>
    </nav>
    <div class="container">';

    $stmt = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $id_to_edit = $row['patient_id'];
    } else {
        echo "<div class='container'><div class='alert alert-danger'>Error: No patient record found.</div></div>";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $id_to_edit; 

    $name = $conn->real_escape_string(trim($_POST['name']));
    $dob = $_POST['dob'];
    $join_date = $_POST['join_date'];
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $address = $conn->real_escape_string(trim($_POST['address']));

    $email = isset($_POST['email']) ? $conn->real_escape_string(trim($_POST['email'])) : '';

    if ($dob > date('Y-m-d')) {
        $error = "❌ Date of Birth cannot be in the future!";
    } else {

        $check = $conn->query("SELECT patient_id FROM patients WHERE phone = '$phone' AND patient_id != '$id'");

        if ($check->num_rows > 0) {
            $error = "❌ Error: The phone number '$phone' is already taken.";
        } else {

            if ($role === 'admin' && !empty($email)) {

                $u_check = $conn->query("SELECT user_id FROM patients WHERE patient_id = '$id'");
                $u_row = $u_check->fetch_assoc();
                $linked_uid = $u_row['user_id'];

                if ($linked_uid) {

                    $e_check = $conn->query("SELECT user_id FROM users WHERE email = '$email' AND user_id != '$linked_uid'");
                    if ($e_check->num_rows > 0) {
                        $error = "❌ Error: The email '$email' is already used by another account.";

                        goto end_of_post; 
                    }

                    $conn->query("UPDATE users SET email = '$email' WHERE user_id = '$linked_uid'");
                }
            }

            $sql = "UPDATE patients SET 
                    name='$name', 
                    dob='$dob', 
                    join_date='$join_date', 
                    phone='$phone', 
                    address='$address' 
                    WHERE patient_id='$id'";

            if ($conn->query($sql) === TRUE) {
                $_SESSION['status'] = "✅ Profile updated successfully!";
                if ($role === 'admin') {
                    echo "<script>window.location.href='list.php';</script>";
                } else {
                    echo "<script>window.location.href='../patient_dashboard.php';</script>";
                }
                exit();
            } else {
                $error = "Database Error: " . $conn->error;
            }
        }
    }

    end_of_post:; 

}

$sql = "SELECT p.*, u.email 
        FROM patients p 
        LEFT JOIN users u ON p.user_id = u.user_id 
        WHERE p.patient_id = '$id_to_edit'";

$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>

<div class="<?php echo ($role === 'admin') ? 'container my-4' : 'my-4'; ?>">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <?php echo ($role === 'admin') ? 'Edit Patient Details' : 'Edit My Profile'; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="edit.php<?php echo ($role === 'admin') ? '?id='.$id_to_edit : ''; ?>" method="POST">
                        <input type="hidden" name="patient_id" value="<?php echo $row['patient_id']; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control"
                                value="<?php echo htmlspecialchars($row['name']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" 
                                    value="<?php echo $row['dob']; ?>"
                                    max="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Join Date</label>
                                <input type="date" name="join_date" class="form-control"
                                    value="<?php echo $row['join_date']; ?>" 
                                    <?php if($role !== 'admin') echo 'readonly style="background-color: #e9ecef;"'; ?> required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control"
                                value="<?php echo htmlspecialchars($row['phone']); ?>" 
                                pattern="[0-9]{10}" maxlength="10"
                                <?php if($role !== 'admin') echo 'readonly style="background-color: #e9ecef;"'; ?> required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="email" name="email" class="form-control"
                                    value="<?php echo htmlspecialchars($row['email']); ?>" 
                                    <?php if($role !== 'admin') echo 'readonly style="background-color: #e9ecef;"'; ?> required>
                            </div>
                            <?php if($role !== 'admin'): ?>
                                <small class="text-muted">To change your phone number and email, please contact the hospital admin.</small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Address</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($row['address']); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?php echo ($role === 'admin') ? 'list.php' : '../patient_dashboard.php'; ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-warning fw-bold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
if ($role === 'admin') {
    require_once '../includes/footer.php'; 
} else {
    echo '</body></html>';
}
?>