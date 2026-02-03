<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$my_user_id = $_SESSION['user_id'];

$my_name = "Guest";
$my_phone = "";
$history = null;
$patient_data = null;

$sql = "SELECT * FROM patients WHERE user_id = '$my_user_id'";
$result = $conn->query($sql);
$patient_data = $result->fetch_assoc();

if ($patient_data) {

    $my_name = $patient_data['name'];
    $my_phone = $patient_data['phone'];

    $p_id = $patient_data['patient_id'];

    $visit_sql = "SELECT *, DATEDIFF(CURDATE(), visit_date) as days_ago 
                  FROM visits 
                  WHERE patient_id = '$p_id' 
                  ORDER BY visit_date DESC";
    $history = $conn->query($visit_sql);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
        <div class="container">
            <span class="navbar-brand mb-0 h1">Patient Portal</span>
            <a href="logout.php" class="btn btn-light btn-sm fw-bold">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <img src="assets/icons/profile.png" width="80" class="mb-3" alt="Profile">

                        <h4 class="fw-bold"><?php echo htmlspecialchars($my_name); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($my_phone); ?></p>
                        <hr>

                        <div class="text-start">
                            <?php if ($patient_data): ?>
                                <p class="mb-2"><strong>Joined:</strong>
                                    <?php echo date('d-M-Y', strtotime($patient_data['join_date'])); ?></p>
                                <p class="mb-3">
                                    <strong>Address:</strong><br><?php echo nl2br(htmlspecialchars($patient_data['address'])); ?>
                                </p>

                                <a href="patients/edit.php" class="btn btn-warning w-100 fw-bold">
                                    <img src="assets/icons/edit.png" width="16" class="me-1"> Edit My Profile
                                </a>

                            <?php else: ?>
                                <div class="alert alert-warning small">
                                    ⚠️ Account active, but no medical record linked.<br>
                                    Please contact the clinic reception.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm h-100 overflow-hidden">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary">My Visit History</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($history && $history->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0 align-middle">
                                    <thead class="table-light text-secondary small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Date</th>
                                            <th>Details</th>
                                            <th>Next Follow-up</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $history->fetch_assoc()): ?>
                                            <tr>
                                                <td class="ps-4 text-nowrap fw-bold">
                                                    <?php echo date('d-M-Y', strtotime($row['visit_date'])); ?>
                                                    <div class="small text-muted fw-normal"><?php echo $row['days_ago']; ?> days
                                                        ago</div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span>Consultation:
                                                            <strong>₹<?php echo $row['consultation_fee']; ?></strong></span>
                                                        <small class="text-muted">Lab Fee:
                                                            ₹<?php echo $row['lab_fee']; ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($row['follow_up_due'] > date('Y-m-d')): ?>
                                                        <span class="badge bg-success rounded-pill px-3">
                                                            <?php echo date('d-M-Y', strtotime($row['follow_up_due'])); ?>
                                                        </span>
                                                    <?php elseif ($row['follow_up_due'] == '0000-00-00' || $row['follow_up_due'] == NULL): ?>
                                                        <span class="text-muted small">-</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary rounded-pill px-3">
                                                            <?php echo date('d-M-Y', strtotime($row['follow_up_due'])); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <p class="text-muted mb-0">No visit history found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>