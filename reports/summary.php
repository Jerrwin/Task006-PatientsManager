<?php
require_once '../config/db.php';
require_once '../includes/header.php';

$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : "last_visit_desc";

$where_sql = "1=1";
if (!empty($search)) {
    $where_sql = "p.name LIKE '%$search%'";
}

switch ($sort_option) {
    case 'name_asc':
        $order_sql = "p.name ASC";
        break;
    case 'name_desc':
        $order_sql = "p.name DESC";
        break;
    case 'visits_high':
        $order_sql = "total_visits DESC";
        break;
    case 'visits_low':
        $order_sql = "total_visits ASC";
        break;
    case 'recent_visit':
        $order_sql = "last_visit DESC";
        break;
    case 'oldest_visit':
        $order_sql = "last_visit ASC";
        break;
    case 'status':
        $order_sql = "status_label ASC";
        break;
    default:
        $order_sql = "last_visit DESC";
}


$count_sql = "SELECT COUNT(*) FROM patients p WHERE $where_sql";
$count_res = $conn->query($count_sql);
$total_rows = $count_res->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT 
            p.name,
            p.phone,
            
            -- 1. Age
            TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) AS age,

            -- 2. Total Visits (Subquery)
            (SELECT COUNT(*) FROM visits v WHERE v.patient_id = p.patient_id) AS total_visits,

            -- 3. Last Visit Date (Subquery)
            (SELECT MAX(visit_date) FROM visits v WHERE v.patient_id = p.patient_id) AS last_visit,

            -- 4. Days Since Last Visit
            DATEDIFF(CURDATE(), (SELECT MAX(visit_date) FROM visits v WHERE v.patient_id = p.patient_id)) AS days_since_last,

            -- 5. Next Follow Up
            DATE_ADD((SELECT MAX(visit_date) FROM visits v WHERE v.patient_id = p.patient_id), INTERVAL 7 DAY) AS next_followup,

            -- 6. STATUS LOGIC
            CASE 
                WHEN (SELECT COUNT(*) FROM visits v WHERE v.patient_id = p.patient_id) = 0 THEN 'No Visits'
                WHEN DATEDIFF(CURDATE(), (SELECT MAX(visit_date) FROM visits v WHERE v.patient_id = p.patient_id)) >= 180 THEN 'Inactive'
                ELSE 'Active'
            END AS status_label

        FROM patients p
        WHERE $where_sql
        ORDER BY $order_sql
        LIMIT $start, $limit";

$result = $conn->query($sql);
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">Hospital Analytics Dashboard</h2>

    <div class="row mb-4 g-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100 shadow">
                <div class="card-body text-center">
                    <h4>
                        <img src="../assets/icons/calendar.png" alt="" width="26" height="26"
                            style="filter: brightness(0) invert(1); margin-bottom: 4px;">
                        Follow-Up Report
                    </h4>
                    <a href="followups.php" class="btn btn-light text-primary fw-bold stretched-link">View Details</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white h-100 shadow">
                <div class="card-body text-center">
                    <h4>
                        <img src="../assets/icons/trends.png" alt="" width="26" height="26"
                            style="filter: brightness(0) invert(1); margin-bottom: 5px;">
                        Monthly Trends
                    </h4>
                    <a href="monthly.php" class="btn btn-light text-success fw-bold stretched-link">View Details</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark h-100 shadow">
                <div class="card-body text-center">
                    <h4>
                        <img src="../assets/icons/birthday-cake.png" alt="" width="26" height="26"
                            style="margin-bottom: 4px;">
                        Birthday Forecast
                    </h4>
                    <a href="birthdays.php" class="btn btn-dark text-warning fw-bold stretched-link">View Details</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Master Patient Summary</h5>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="Search by Name..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="recent_visit" <?php if ($sort_option == 'recent_visit')
                                echo 'selected'; ?>>Most
                                Recent Visit</option>
                            <option value="oldest_visit" <?php if ($sort_option == 'oldest_visit')
                                echo 'selected'; ?>>
                                Oldest Visit</option>
                            <option value="visits_high" <?php if ($sort_option == 'visits_high')
                                echo 'selected'; ?>>Total
                                Visits (High)</option>
                            <option value="visits_low" <?php if ($sort_option == 'visits_low')
                                echo 'selected'; ?>>Total
                                Visits (Low)</option>
                            <option value="name_asc" <?php if ($sort_option == 'name_asc')
                                echo 'selected'; ?>>Name (A-Z)
                            </option>
                            <option value="status" <?php if ($sort_option == 'status')
                                echo 'selected'; ?>>Status</option>
                        </select>
                        <button class="btn btn-outline-secondary" type="submit">Go</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow overflow-hidden">

        <div class="table-responsive">
            <table class="table table-hover mb-0 text-center align-middle">
                <thead class="table-dark text-white">
                    <tr>
                        <th class="text-start text-nowrap">Patient Name</th>
                        <th class="text-nowrap">Age</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-nowrap">Total Visits</th>
                        <th class="text-nowrap">Last Visit</th>
                        <th class="text-nowrap">Days Since</th>
                        <th class="text-nowrap">Next Follow-Up</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-start text-nowrap">
                                    <span class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></span><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['phone']); ?></small>
                                </td>

                                <td class="text-nowrap"><?php echo $row['age']; ?></td>

                                <td class="text-nowrap">
                                    <?php if ($row['status_label'] == 'Inactive'): ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php elseif ($row['status_label'] == 'No Visits'): ?>
                                        <span class="badge bg-warning text-dark">No Visits Yet</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-nowrap">
                                    <span class="badge bg-secondary rounded-pill">
                                        <?php echo $row['total_visits']; ?>
                                    </span>
                                </td>

                                <td class="text-nowrap">
                                    <?php echo $row['last_visit'] ? date('d-M-Y', strtotime($row['last_visit'])) : '-'; ?>
                                </td>

                                <td class="text-nowrap">
                                    <?php echo ($row['days_since_last'] !== NULL) ? $row['days_since_last'] . " days" : '-'; ?>
                                </td>

                                <td class="text-nowrap">
                                    <?php echo $row['next_followup'] ? date('d-M-Y', strtotime($row['next_followup'])) : '-'; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No records found matching your search.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div> <?php if ($total_pages > 1): ?>
            <nav class="card-footer d-flex justify-content-end">
                <ul class="pagination mb-0">
                    <li class="page-item <?php if ($page <= 1)
                        echo 'disabled'; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&sort=<?php echo $sort_option; ?>">Previous</a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if ($page == $i)
                            echo 'active'; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&sort=<?php echo $sort_option; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?php if ($page >= $total_pages)
                        echo 'disabled'; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&sort=<?php echo $sort_option; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>