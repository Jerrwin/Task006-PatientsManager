<?php
require_once '../config/db.php';
require_once '../includes/header.php';

$sql = "SELECT 
            name, 
            dob,
            DATE_FORMAT(dob, '%d-%M') AS bday_date,
            TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS current_age,
            
            -- Calculate Age they are turning THIS YEAR
            (YEAR(CURDATE()) - YEAR(dob)) AS turning_age

        FROM patients
        WHERE 
            -- Logic: Birthday is within next 30 days
            (DATE_ADD(dob, INTERVAL (YEAR(CURDATE()) - YEAR(dob)) YEAR) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY))
            
            OR
            
            -- Logic: Milestone Ages (Turning 40, 50, 60 this year)
            (YEAR(CURDATE()) - YEAR(dob)) IN (40, 50, 60)

        ORDER BY turning_age DESC";

$result = $conn->query($sql);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><img src="../assets/icons/birthday-cake.png" alt="" width="26" height="26" style="margin-bottom: 5px;"> Birthday & Milestone Report</h3>
        <a href="summary.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <div class="alert alert-info">
        Showing patients who have a <strong>birthday in the next 30 days</strong> OR are turning <strong>40, 50, or 60</strong> this year.
    </div>

    <div class="card shadow-sm">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Patient Name</th>
                    <th>Date of Birth</th>
                    <th>Birthday</th>
                    <th>Current Age</th>
                    <th>Turning Age</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php 
                            $is_milestone = in_array($row['turning_age'], [40, 50, 60]);
                        ?>
                        <tr class="<?php echo $is_milestone ? 'table-warning' : ''; ?>">
                            <td class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo $row['dob']; ?></td>
                            <td><?php echo $row['bday_date']; ?></td>
                            <td><?php echo $row['current_age']; ?></td>
                            <td class="fw-bold"><?php echo $row['turning_age']; ?></td>
                            <td>
                                <?php if ($is_milestone): ?>
                                    <span class="badge bg-warning text-dark">Milestone!</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">Upcoming</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-4">No upcoming birthdays or milestones found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>