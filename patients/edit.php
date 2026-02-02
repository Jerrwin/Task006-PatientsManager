<?php
session_start();
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>window.location.href='../login.php';</script>";
    exit();
}

$error = ""; 

$row = [];   

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['patient_id'];
    $row['patient_id'] = $id;
    $row['name'] = $_POST['name'];
    $row['dob'] = $_POST['dob'];
    $row['join_date'] = $_POST['join_date'];
    $row['phone'] = $_POST['phone'];
    $row['address'] = $_POST['address'];

    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $name = $conn->real_escape_string(trim($_POST['name']));
    $address = $conn->real_escape_string(trim($_POST['address']));

    if ($row['dob'] > date('Y-m-d')) {
        $error = "❌ Date of Birth cannot be in the future!";
    } else {

        $check_phone = $conn->query("SELECT patient_id FROM patients WHERE phone = '$phone' AND patient_id != '$id'");

        if ($check_phone->num_rows > 0) {
            $error = "❌ Error: The phone number '$phone' is already taken by another patient.";
        } else {

            $sql = "UPDATE patients SET 
                    name='$name', 
                    dob='{$row['dob']}', 
                    join_date='{$row['join_date']}', 
                    phone='$phone', 
                    address='$address' 
                    WHERE patient_id='$id'";

            if ($conn->query($sql) === TRUE) {
                $_SESSION['status'] = "✅ Patient details updated successfully!";
                echo "<script>window.location.href='list.php';</script>"; 

                exit();
            } else {
                $error = "Database Error: " . $conn->error;
            }
        }
    }
}

if (empty($row)) {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM patients WHERE patient_id = '$id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            echo "<div class='container mt-5'><div class='alert alert-danger'>Patient not found!</div></div>";
            exit();
        }
    } else {
        echo "<script>window.location.href='list.php';</script>";
        exit();
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-warning text-dark d-flex align-items-center gap-2">
                    <h4 class="mb-0">Edit Patient Details</h4>
                </div>
                <div class="card-body">
                    <form action="edit.php" method="POST">
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
                                    value="<?php echo $row['join_date']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">📞</span>
                                <input type="text" name="phone" class="form-control"
                                    value="<?php echo htmlspecialchars($row['phone']); ?>" 
                                    pattern="[0-9]{10}" maxlength="10" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Address</label>
                            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($row['address']); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="list.php" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-warning px-4 fw-bold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>