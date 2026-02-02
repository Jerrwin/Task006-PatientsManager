<?php
require_once '../includes/header.php';
require_once '../config/db.php';

$limit = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$search = "";
$search_query = "1=1";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $search_query = "(name LIKE '%$search%' OR phone LIKE '%$search%')";
}

$sort_option = isset($_GET['sort_option']) ? $_GET['sort_option'] : 'newest';
switch ($sort_option) {
    case 'name_asc':
        $sort = "name";
        $order = "ASC";
        break;
    case 'name_desc':
        $sort = "name";
        $order = "DESC";
        break;
    case 'age_asc':
        $sort = "dob";
        $order = "DESC";
        break;
    case 'age_desc':
        $sort = "dob";
        $order = "ASC";
        break;
    case 'oldest':
        $sort = "patient_id";
        $order = "ASC";
        break;
    default:
        $sort = "patient_id";
        $order = "DESC";
        break;
}

$total_sql = "SELECT COUNT(*) FROM patients WHERE $search_query";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);


$sql = "SELECT 
            p.patient_id, 
            p.name, 
            p.phone,
            p.dob,
            
            -- SQL Requirement: Age in Years
            TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) AS age_years,

            -- SQL Requirement: Full Age (Years + Months)
            CONCAT(
                TIMESTAMPDIFF(YEAR, p.dob, CURDATE()), ' Yrs, ', 
                (TIMESTAMPDIFF(MONTH, p.dob, CURDATE()) % 12), ' Mos'
            ) AS full_age,

            -- SQL Requirement: Formatted Join Date
            DATE_FORMAT(p.join_date, '%d-%b-%Y') AS formatted_join_date,

            -- SQL Requirement: Total Visits (Subquery)
            (SELECT COUNT(*) FROM visits v WHERE v.patient_id = p.patient_id) AS total_visits

        FROM patients p
        WHERE $search_query 
        ORDER BY $sort $order 
        LIMIT $start, $limit";

$result = $conn->query($sql);
?>

<div class="container mt-4">

    <?php if (isset($_SESSION['status'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['status'];
            unset($_SESSION['status']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center g-3">

                <div class="col-12 col-md-3">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                        data-bs-target="#addPatientModal">
                        + Add New Patient
                    </button>
                </div>

                <div class="col-12 col-md-5">
                    <form action="list.php" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search name or phone..."
                            value="<?php echo htmlspecialchars($search); ?>">
                        <input type="hidden" name="sort_option" value="<?php echo $sort_option; ?>">
                        <button type="submit" class="btn btn-outline-secondary me-2">Search</button>
                        <?php if (!empty($search)): ?>
                            <a href="list.php" class="btn btn-danger">X</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="col-12 col-md-4">
                    <form action="list.php" method="GET">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group">
                            <label class="input-group-text bg-light">Sort By</label>
                            <select name="sort_option" class="form-select" onchange="this.form.submit()">
                                <option value="newest" <?php if ($sort_option == 'newest')
                                    echo 'selected'; ?>>Newest
                                    First</option>
                                <option value="oldest" <?php if ($sort_option == 'oldest')
                                    echo 'selected'; ?>>Oldest
                                    First</option>
                                <option value="name_asc" <?php if ($sort_option == 'name_asc')
                                    echo 'selected'; ?>>Name
                                    (A-Z)</option>
                                <option value="age_asc" <?php if ($sort_option == 'age_asc')
                                    echo 'selected'; ?>>Age
                                    (Youngest)</option>
                                <option value="age_desc" <?php if ($sort_option == 'age_desc')
                                    echo 'selected'; ?>>Age
                                    (Oldest)</option>
                            </select>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <div class="card shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr class="text-center">
                            <th class="text-nowrap">Name</th>
                            <th>Age</th>
                            <th class="text-nowrap">Joined Date</th>
                            <th class="text-nowrap">Phone</th>
                            <th class="text-nowrap">Total Visits</th>
                            <th class="text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-nowrap"><?php echo htmlspecialchars($row['name']); ?></td>

                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?php echo $row['full_age']; ?>
                                        </span>
                                    </td>

                                    <td class="text-nowrap"><?php echo $row['formatted_join_date']; ?></td>

                                    <td class="text-nowrap"><?php echo htmlspecialchars($row['phone']); ?></td>

                                    <td>
                                        <span class="badge bg-secondary rounded-pill">
                                            <?php echo $row['total_visits']; ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1 text-nowrap">
                                            <a href="view.php?id=<?php echo $row['patient_id']; ?>"
                                                class="btn btn-sm btn-info text-white d-flex align-items-center justify-content-center"
                                                title="View Profile" style="width: 32px; height: 32px;">
                                                <img src="../assets/icons/view.png" alt="View"
                                                    style="width: 20px; height: 20px; filter: brightness(0) invert(1);">
                                            </a>

                                            <a href="edit.php?id=<?php echo $row['patient_id']; ?>"
                                                class="btn btn-sm btn-warning d-flex align-items-center justify-content-center"
                                                title="Edit Details" style="width: 32px; height: 32px;">
                                                <img src="../assets/icons/edit.png" alt="Edit"
                                                    style="width: 16px; height: 16px;">
                                            </a>

                                            <a href="delete.php?id=<?php echo $row['patient_id']; ?>"
                                                class="btn btn-sm btn-danger d-flex align-items-center justify-content-center"
                                                title="Delete Patient"
                                                onclick="return confirm('⚠️ WARNING: This will delete the patient AND all their visit history.\n\nAre you sure?');"
                                                style="width: 32px; height: 32px;">
                                                <img src="../assets/icons/delete.png" alt="Delete"
                                                    style="width: 16px; height: 16px;">
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No patients found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end">
            <nav>
                <ul class="pagination mb-0">
                    <li class="page-item <?php if ($page <= 1)
                        echo 'disabled'; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>">Previous</a>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                    </li>
                    <li class="page-item <?php if ($page >= $total_pages)
                        echo 'disabled'; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="modal fade" id="addPatientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill"></i> Add New Patient</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form action="add.php" method="POST" autocomplete="off">
                <div class="modal-body">

                    <h6 class="text-primary fw-bold mb-3">1. Login Credentials</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Username</label>
                            <input type="text" name="username" class="form-control" required
                                autocomplete="new-password">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Password</label>
                            <input type="password" name="password" class="form-control"
                                required autocomplete="new-password">
                        </div>
                    </div>

                    <hr class="text-muted">

                    <h6 class="text-primary fw-bold mb-3">2. Patient Details</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" pattern="[0-9]{10}" maxlength="10"
                                placeholder="10 Digits" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Date of Birth</label>
                            <input type="date" name="dob" class="form-control" max="<?php echo date('Y-m-d'); ?>"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Join Date</label>
                            <input type="date" name="join_date" class="form-control"
                                value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small text-muted">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>