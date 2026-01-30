<?php
require_once '../includes/header.php';
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['patient_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $dob = $_POST['dob'];
    $join_date = $_POST['join_date'];
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);

    if ($dob > date('Y-m-d')) {
        echo "<div class='alert alert-danger'>❌ Date of Birth cannot be in the future!</div>";
    } else {
        $sql = "UPDATE patients SET 
                name='$name', 
                dob='$dob', 
                join_date='$join_date', 
                phone='$phone', 
                address='$address' 
                WHERE patient_id='$id'";

        if ($conn->query($sql) === TRUE) {
            if (session_status() === PHP_SESSION_NONE)
                session_start();
            $_SESSION['status'] = "✅ Patient updated successfully!";
            echo "<script>window.location.href='list.php';</script>";
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}

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
?>

<div class="container mt-3">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Edit Patient Details</h4>
                </div>
                <div class="card-body">
                    <form action="edit.php" method="POST">
                        <input type="hidden" name="patient_id" value="<?php echo $row['patient_id']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control"
                                value="<?php echo htmlspecialchars($row['name']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="<?php echo $row['dob']; ?>"
                                    max="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Join Date</label>
                                <input type="date" name="join_date" class="form-control"
                                    value="<?php echo $row['join_date']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control"
                                value="<?php echo htmlspecialchars($row['phone']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control"
                                rows="3"><?php echo htmlspecialchars($row['address']); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-warning">Update Patient</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>