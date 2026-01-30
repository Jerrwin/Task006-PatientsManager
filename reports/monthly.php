<?php
require_once '../config/db.php';
require_once '../includes/header.php';

// --- 1. FETCH VISIT DATA (For Table & Chart) ---
$visit_sql = "SELECT 
                DATE_FORMAT(visit_date, '%Y-%m') AS month_key,
                -- FIX: Show Month AND Year (e.g., 'January 2025')
                DATE_FORMAT(visit_date, '%M %Y') AS month_name, 
                COUNT(*) AS visit_count,
                SUM(consultation_fee + lab_fee) AS total_revenue
              FROM visits 
              WHERE visit_date IS NOT NULL AND visit_date != '0000-00-00' -- FIX: Remove empty rows
              AND visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
              GROUP BY month_key, month_name 
              ORDER BY month_key ASC"; 
$visit_res = $conn->query($visit_sql);

// STORE DATA
$chart_labels = [];
$chart_data = [];
$table_data = [];

while ($row = $visit_res->fetch_assoc()) {
    $chart_labels[] = $row['month_name'];
    $chart_data[] = $row['visit_count'];
    $table_data[] = $row;
}

// --- 2. FETCH PATIENT DATA (For Table Only) ---
$join_sql = "SELECT 
                DATE_FORMAT(join_date, '%Y-%m') AS month_key,
                -- FIX: Show Month AND Year here too
                DATE_FORMAT(join_date, '%M %Y') AS month_name, 
                COUNT(*) AS patient_count
             FROM patients 
             WHERE join_date IS NOT NULL AND join_date != '0000-00-00' -- FIX: Remove empty rows
             GROUP BY month_key, month_name 
             ORDER BY month_key DESC LIMIT 6";
$join_res = $conn->query($join_sql);
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>
            <img src="../assets/icons/trends.png" alt="" width="26" height="26" style="margin-bottom: 4px;"> 
            Monthly Performance Report
        </h3>
        <a href="summary.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Visits & Revenue Details</h5>
                </div>
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>Month & Year</th> <th>Total Visits</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $reversed_table = array_reverse($table_data);
                        if (!empty($reversed_table)):
                            foreach ($reversed_table as $row): 
                        ?>
                            <tr>
                                <td><?php echo $row['month_name']; ?></td>
                                <td class="fw-bold"><?php echo $row['visit_count']; ?></td>
                                <td class="text-success">₹<?php echo $row['total_revenue']; ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="3" class="text-center">No visits found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">New Patient Registrations</h5>
                </div>
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>Month & Year</th> <th>New Patients</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($join_res->num_rows > 0): ?>
                            <?php while ($row = $join_res->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['month_name']; ?></td>
                                    <td class="fw-bold"><?php echo $row['patient_count']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="2" class="text-center">No new patients.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="col-12 mt-4"> 
            <div class="card shadow">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Visits Trend (Last 6 Months)</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Monthly Visits',
                data: <?php echo json_encode($chart_data); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 3,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>