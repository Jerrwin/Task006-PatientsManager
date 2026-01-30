<?php
require_once '../config/db.php';
require_once '../includes/header.php';

$limit = 5; 
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$search = "";
$where_clause = "1=1"; 

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_clause = "p.name LIKE '%$search%'"; 
}

$count_sql = "SELECT COUNT(*) FROM visits v JOIN patients p ON v.patient_id = p.patient_id WHERE $where_clause";
$count_res = $conn->query($count_sql);
$total_rows = $count_res->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT 
            v.visit_id,
            v.visit_date,
            v.follow_up_due,
            p.name AS patient_name,
            p.patient_id,

            -- SQL Calc: Days Since Visit
            DATEDIFF(CURDATE(), v.visit_date) AS days_since_visit,

            -- SQL Calc: Status Logic
            CASE 
                WHEN v.follow_up_due < CURDATE() THEN 'Overdue'
                WHEN v.follow_up_due = CURDATE() THEN 'Due Today'
                ELSE 'Upcoming'
            END AS follow_up_status

        FROM visits v
        JOIN patients p ON v.patient_id = p.patient_id
        WHERE $where_clause
        ORDER BY v.visit_date DESC
        LIMIT $start, $limit";

$result = $conn->query($sql);
?>

<div class="container mt-4">
    
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h2><img src="../assets/icons/calendar.png" alt="" width="34" height="34"> Visit Registry</h2>
        </div>
        <div class="col-md-6">
            <div class="d-flex gap-2">
                <form class="d-flex flex-grow-1" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search Patient Name..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                    <?php if($search): ?>
                        <a href="list.php" class="btn btn-outline-danger">X</a>
                    <?php endif; ?>
                </form>
                <a href="add.php" class="btn btn-success text-nowrap">+ New Visit</a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Days Ago</th>
                        <th>Follow-Up</th>
                        <th>Status</th>
                        <th>History</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d-M-Y', strtotime($row['visit_date'])); ?></td>
                                <td>
                                    <a href="patient_visits.php?id=<?php echo $row['patient_id']; ?>" class="fw-bold text-decoration-none">
                                        <?php echo htmlspecialchars($row['patient_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo $row['days_since_visit']; ?> days</td>
                                <td><?php echo date('d-M-Y', strtotime($row['follow_up_due'])); ?></td>
                                <td>
                                    <?php if ($row['follow_up_status'] == 'Overdue'): ?>
                                        <span class="badge bg-danger">Overdue</span>
                                    <?php elseif ($row['follow_up_status'] == 'Due Today'): ?>
                                        <span class="badge bg-warning text-dark">Due Today</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Upcoming</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="patient_visit.php?id=<?php echo $row['patient_id']; ?>" class="btn btn-sm btn-info text-white"><img src="../assets/icons/history.png" alt="History" width="16" height="16"></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4">No visits found matching your search.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav class="mt-3 d-flex justify-content-end">
        <ul class="pagination">
            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>">Previous</a>
            </li>
            
            <?php for($i=1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>">Next</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>