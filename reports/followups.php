<?php
require_once '../config/db.php';
require_once '../includes/header.php';

$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) 
              FROM visits v
              JOIN patients p ON v.patient_id = p.patient_id
              WHERE v.follow_up_due BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                                        AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

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
        
        WHERE v.follow_up_due BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                                  AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY v.follow_up_due ASC
        LIMIT $start, $limit"; 

$result = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <h3 class="m-0 text-center text-md-start">
            <img src="../assets/icons/calendar.png" alt="" width="26" height="26" style="margin-bottom: 4px;"> 
            Follow-Up Report
        </h3>
        <a href="summary.php" class="btn btn-outline-secondary w-10 w-md-auto">
            Back to Dashboard
        </a>
    </div>

    <div class="card shadow-sm overflow-hidden mb-4">

        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-dark text-center">
                    <tr>
                        <th class="text-nowrap">Patient</th>
                        <th class="text-nowrap">Last Visit</th>
                        <th class="text-nowrap">Follow-Up Date</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold text-nowrap"><?php echo htmlspecialchars($row['name']); ?></td>

                                <td class="text-nowrap"><?php echo date('d-M-Y', strtotime($row['visit_date'])); ?></td>
                                <td class="text-nowrap"><?php echo date('d-M-Y', strtotime($row['follow_up_due'])); ?></td>

                                <td class="text-nowrap">
                                    <?php if ($row['status'] == 'Overdue'): ?>
                                        <span class="badge bg-danger">Overdue by <?php echo $row['days_overdue']; ?> days</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Upcoming</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-nowrap">
                                    <a href="tel:<?php echo $row['phone']; ?>"
                                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                        <img src="../assets/icons/telephone.png" alt="" width="16" height="16"> Call
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-4 text-muted">No follow-ups found in this period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-end">
                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>