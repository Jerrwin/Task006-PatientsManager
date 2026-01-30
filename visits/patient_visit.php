<?php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    echo "<div class='container mt-5 text-center'><h3>❌ No Patient Selected</h3></div>";
    exit();
}

$patient_id = $_GET['id'];

$p_sql = "SELECT name, phone FROM patients WHERE patient_id = '$patient_id'";
$patient = $conn->query($p_sql)->fetch_assoc();

$stats_sql = "SELECT 
                COUNT(*) AS total_visits,
                
                -- SQL Calc: Date difference between first ever visit and last visit
                DATEDIFF(MAX(visit_date), MIN(visit_date)) AS days_span,
                
                SUM(consultation_fee + lab_fee) AS total_spent
              FROM visits 
              WHERE patient_id = '$patient_id'";

$stats = $conn->query($stats_sql)->fetch_assoc();

$hist_sql = "SELECT *, 
                DATEDIFF(CURDATE(), visit_date) AS days_ago
             FROM visits 
             WHERE patient_id = '$patient_id' 
             ORDER BY visit_date DESC";
$history = $conn->query($hist_sql);
?>

<div class="container mt-4">
    <div class="card mb-4 p-2 border-primary bg-light">
    <div class="card-body">
        
        <div class="text-center mb-4">
            <h2 class="text-primary fw-bold mb-0">
                <i class="bi bi-person-circle"></i> 
                <?php echo htmlspecialchars($patient['name']); ?>
            </h2>
            <p class="text-muted mb-0">
                <i class="bi bi-telephone-fill small"></i> 
                <?php echo htmlspecialchars($patient['phone']); ?>
            </p>
        </div>
        
        <div class="row text-center">
            <div class="col-md-4 mb-3 mb-md-0"> <div class="card bg-white shadow-sm p-3 h-100"> <h5 class="text-secondary">Total Visits</h5>
                    <h3 class="text-primary fw-bold"><?php echo $stats['total_visits']; ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card bg-white shadow-sm p-3 h-100">
                    <h5 class="text-secondary">History Span</h5>
                    <h3 class="text-info fw-bold">
                        <?php echo $stats['days_span'] ? $stats['days_span'] : 0; ?> Days
                    </h3>
                    <small class="text-muted" style="font-size: 0.8rem;">(First to Last Visit)</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-white shadow-sm p-3 h-100">
                    <h5 class="text-secondary">Total Spent</h5>
                    <h3 class="text-success fw-bold">₹<?php echo number_format($stats['total_spent'] ?? 0); ?></h3>
                </div>
            </div>
        </div>

    </div>
</div>

    <h4 class="mb-3">Visit History</h4>
    <div class="card shadow-sm">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Visit Date</th>
                    <th>Days Ago</th>
                    <th>Consultation</th>
                    <th>Lab Fee</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($history->num_rows > 0): ?>
                    <?php while ($row = $history->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d-M-Y', strtotime($row['visit_date'])); ?></td>
                            <td><?php echo $row['days_ago']; ?> days</td>
                            <td>₹<?php echo $row['consultation_fee']; ?></td>
                            <td>₹<?php echo $row['lab_fee']; ?></td>
                            <td class="fw-bold">₹<?php echo $row['consultation_fee'] + $row['lab_fee']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-4">No history found for this patient.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="mt-3">
        <a href="list.php" class="btn btn-secondary">Back to Visits List</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>