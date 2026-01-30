<?php
require_once '../config/db.php';
require_once '../includes/header.php';

$sql = "SELECT 
            p.name, 
            p.phone,
            v.visit_date,
            v.follow_up_due,
            DATEDIFF(CURDATE(), v.follow_up_due) AS days_overdue,
            
            CASE 
                WHEN v.follow_up_due < CURDATE() THEN 'Overdue'
                ELSE 'Upcoming'
            END AS status

        FROM visits v
        JOIN patients p ON v.patient_id = p.patient_id
        
        -- Filter: Only show active follow-ups (Last 30 days of activity) or future ones
        WHERE v.follow_up_due BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                                  AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY v.follow_up_due ASC";

$result = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><img src="../assets/icons/calendar.png" alt="" width="26" height="26" style="margin-bottom: 4px;"> Follow-Up Status Report</h3>
        <a href="summary.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <div class="card shadow-sm">
        <table class="table table-striped mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Patient</th>
                    <th>Last Visit</th>
                    <th>Follow-Up Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo date('d-M-Y', strtotime($row['visit_date'])); ?></td>
                        <td><?php echo date('d-M-Y', strtotime($row['follow_up_due'])); ?></td>
                        <td>
                            <?php if ($row['status'] == 'Overdue'): ?>
                                <span class="badge bg-danger">Overdue by <?php echo $row['days_overdue']; ?> days</span>
                            <?php else: ?>
                                <span class="badge bg-success">Upcoming</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="tel:<?php echo $row['phone']; ?>" class="btn btn-sm btn-outline-primary"><img src="../assets/icons/telephone.png" alt="" width="16" height="16"> Call</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>