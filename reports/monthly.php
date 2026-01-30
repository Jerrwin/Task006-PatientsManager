<?php
require_once '../config/db.php';
require_once '../includes/header.php';

// FETCH VISIT DATA (For Table & Chart)
$visit_sql = "SELECT 
                DATE_FORMAT(visit_date, '%Y-%m') AS month_key,
                DATE_FORMAT(visit_date, '%M %Y') AS month_name, 
                COUNT(*) AS visit_count,
                SUM(consultation_fee + lab_fee) AS total_revenue
              FROM visits 
              WHERE visit_date IS NOT NULL AND visit_date != '0000-00-00'
              AND visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
              GROUP BY month_key, month_name 
              ORDER BY month_key ASC"; 
$visit_res = $conn->query($visit_sql);

$chart_labels = [];
$chart_data = [];
$table_data = [];

while ($row = $visit_res->fetch_assoc()) {
    $chart_labels[] = $row['month_name'];
    $chart_data[] = $row['visit_count'];
    $table_data[] = $row;
}

// FETCH PATIENT DATA (For Table Only)
$join_sql = "SELECT 
                DATE_FORMAT(join_date, '%Y-%m') AS month_key,
                DATE_FORMAT(join_date, '%M %Y') AS month_name, 
                COUNT(*) AS patient_count
             FROM patients 
             WHERE join_date IS NOT NULL AND join_date != '0000-00-00'
             GROUP BY month_key, month_name 
             ORDER BY month_key DESC LIMIT 6";
$join_res = $conn->query($join_sql);
?>

<div class="container my-4">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <h3 class="m-0 text-center text-md-start">
            <img src="../assets/icons/trends.png" alt="" width="26" height="26" style="margin-bottom: 4px;"> 
            Monthly Performance
        </h3>
        <a href="summary.php" class="btn btn-outline-secondary w-10 w-md-auto">
        Back to Dashboard
        </a>
    </div>

    <div class="row g-4">
        
        <div class="col-md-6">
            <div class="card shadow-sm h-100 overflow-hidden">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Visits & Revenue</h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table mb-0 table-striped text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap">Month & Year</th> 
                                <th class="text-nowrap">Visits</th>
                                <th class="text-nowrap text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $reversed_table = array_reverse($table_data);
                            if (!empty($reversed_table)):
                                foreach ($reversed_table as $row): 
                            ?>
                                <tr>
                                    <td class="text-nowrap"><?php echo $row['month_name']; ?></td>
                                    <td class="fw-bold"><?php echo $row['visit_count']; ?></td>
                                    <td class="text-success text-end text-nowrap">₹<?php echo number_format($row['total_revenue']); ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="3" class="text-center py-3">No visits found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100 overflow-hidden">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-people-fill"></i> New Patients</h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table mb-0 table-striped text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap">Month & Year</th> 
                                <th class="text-nowrap">New Registrations</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($join_res->num_rows > 0): ?>
                                <?php while ($row = $join_res->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-nowrap"><?php echo $row['month_name']; ?></td>
                                        <td class="fw-bold"><?php echo $row['patient_count']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center py-3">No new patients.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-12"> 
            <div class="card shadow">
                <div class="card-header py-3 bg-white d-flex align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up"></i> Visits Trend (Last 6 Months)
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px; width: 100%;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
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
                fill: true,
                pointRadius: 4, // Bigger dots for easier tapping/viewing on mobile
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(75, 192, 192, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false 
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { borderDash: [5, 5] }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>

<?php require_once '../includes/footer.php'; ?>