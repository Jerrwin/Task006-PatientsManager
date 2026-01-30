<?php
require_once '../config/db.php';
require_once '../includes/header.php';

$patients = $conn->query("SELECT patient_id, name, phone FROM patients ORDER BY name ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = $_POST['patient_id'];
    $visit_date = $_POST['visit_date'];
    $consult_fee = $_POST['consultation_fee'];
    $lab_fee = $_POST['lab_fee'];

    // SQL RULE: Calculate Follow-up Date (Visit + 7 Days) inside SQL
    $sql = "INSERT INTO visits (patient_id, visit_date, consultation_fee, lab_fee, follow_up_due) 
            VALUES ('$patient_id', '$visit_date', '$consult_fee', '$lab_fee', DATE_ADD('$visit_date', INTERVAL 7 DAY))";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['status'] = "✅ Visit recorded! Follow-up set automatically.";
        echo "<script>window.location.href='list.php';</script>";
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Record New Visit</h4>
        </div>
        <div class="card-body">
            <?php if (isset($error))
                echo "<div class='alert alert-danger'>$error</div>"; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Select Patient</label>
                    <select name="patient_id" class="form-select" required>
                        <option value="">-- Choose Patient --</option>
                        <?php while ($p = $patients->fetch_assoc()): ?>
                            <option value="<?php echo $p['patient_id']; ?>">
                                <?php echo htmlspecialchars($p['name']) . " (" . $p['phone'] . ")"; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Visit Date</label>
                    <input type="date" name="visit_date" class="form-control" value="<?php echo date('Y-m-d'); ?>"
                        required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Consultation Fee (₹)</label>
                        <input type="number" name="consultation_fee" class="form-control" placeholder="0.00" step="1.00"
                            min="0" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lab Fee (₹)</label>
                        <input type="number" name="lab_fee" class="form-control" placeholder="0.00" step="1.00" min="0" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100">Save Visit Record</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>