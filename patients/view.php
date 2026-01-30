<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    echo "<script>window.location.href='list.php';</script>";
    exit();
}


$id = $_GET['id'];

// SQL REQUIREMENT: Calculate Profile Stats inside SQL
$sql = "SELECT 
            p.*,
            
            -- 1. Age Calculation
            CONCAT(TIMESTAMPDIFF(YEAR, p.dob, CURDATE()), ' Yrs, ', (TIMESTAMPDIFF(MONTH, p.dob, CURDATE()) % 12), ' Mos') AS full_age,
            
            -- 2. Join Date Formatted
            DATE_FORMAT(p.join_date, '%d-%b-%Y') AS formatted_join,

            -- 3. Last Visit Date
            (SELECT MAX(visit_date) FROM visits v WHERE v.patient_id = p.patient_id) AS last_visit,

            -- 4. Days Since Last Visit
            DATEDIFF(CURDATE(), (SELECT MAX(visit_date) FROM visits v WHERE v.patient_id = p.patient_id)) AS days_since,

            -- 5. Next Follow-up Date
            (SELECT MAX(follow_up_due) FROM visits v WHERE v.patient_id = p.patient_id) AS next_followup,

            -- 6. Follow-up Status
            CASE 
                WHEN (SELECT MAX(follow_up_due) FROM visits v WHERE v.patient_id = p.patient_id) < CURDATE() THEN 'Overdue'
                WHEN (SELECT MAX(follow_up_due) FROM visits v WHERE v.patient_id = p.patient_id) >= CURDATE() THEN 'Upcoming'
                ELSE 'No Visits'
            END AS followup_status

        FROM patients p 
        WHERE p.patient_id = '$id'";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Patient not found.</div>";
    exit();
}

$patient = $result->fetch_assoc();
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="list.php">Patients</a></li>
            <li class="breadcrumb-item active" aria-current="page">Patient Profile</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                        <?php echo strtoupper(substr($patient['name'], 0, 1)); ?>
                    </div>
                    <h4><?php echo htmlspecialchars($patient['name']); ?></h4>
                    <span class="badge bg-secondary"><?php echo $patient['full_age']; ?></span>
                    <hr>
                    <div class="text-start">
                        <p><strong>📞 Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
                        <p><strong>🎂 DOB:</strong> <?php echo $patient['dob']; ?></p>
                        <p><strong>📅 Joined:</strong> <?php echo $patient['formatted_join']; ?></p>
                        <p><strong>🏠 Address:</strong> <?php echo htmlspecialchars($patient['address']); ?></p>
                    </div>
                    <div class="d-grid gap-2 mt-3">
                        <a href="edit.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-warning">Edit Details</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Current Status</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <h6 class="text-muted">Last Visit</h6>
                            <h4 class="text-primary">
                                <?php echo $patient['last_visit'] ? date('d-M-Y', strtotime($patient['last_visit'])) : 'Never'; ?>
                            </h4>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6 class="text-muted">Days Since Visit</h6>
                            <h4>
                                <?php echo $patient['days_since'] !== NULL ? $patient['days_since'] . "<small> days</small>" : '-'; ?>
                            </h4>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h6 class="text-muted">Follow-Up</h6>
                            <?php if ($patient['followup_status'] == 'Overdue'): ?>
                                <h4 class="text-danger">Overdue</h4>
                                <small><?php echo date('d-M-Y', strtotime($patient['next_followup'])); ?></small>
                            <?php elseif ($patient['followup_status'] == 'Upcoming'): ?>
                                <h4 class="text-success">Upcoming</h4>
                                <small><?php echo date('d-M-Y', strtotime($patient['next_followup'])); ?></small>
                            <?php else: ?>
                                <h4 class="text-muted">None</h4>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span>Want to see full medical history?</span>
                <a href="../visits/patient_visit.php?id=<?php echo $patient['patient_id']; ?>" class="btn btn-primary">View Visits History</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>