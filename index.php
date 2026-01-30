<?php
require_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="display-4 text-primary">Patient Record Manager</h1>
        <p class="lead text-muted">Select a module to manage records.</p>
    </div>

    <div class="row justify-content-center">

        <div class="col-md-4 mb-4">
            <div class="card shadow-lg border-0 h-100 text-center hover-card">
                <div class="card-body p-5">
                    <div class="mb-3">
                        <img src="assets/icons/patients.png" alt="Patients Icon" width="64" height="64">
                    </div>
                    <h3 class="card-title">Patients</h3>
                    <p class="card-text text-muted">View and manage patient records.</p>
                    <a href="patients/list.php" class="btn btn-outline-primary btn-lg w-100 stretched-link">
                        Manage Patients
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-lg border-0 h-100 text-center hover-card">
                <div class="card-body p-5">
                    <div class="mb-3">
                        <img src="assets/icons/visit.png" alt="Doctors Icon" width="64" height="64">
                    </div>
                    <h3 class="card-title">Visits</h3>
                    <p class="card-text text-muted">View and manage patient visits.</p>
                    <a href="visits/list.php" class="btn btn-outline-warning btn-lg w-100 stretched-link">
                        Manage Visits
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow-lg border-0 h-100 text-center hover-card">
                <div class="card-body p-5">
                    <div class="mb-3">
                        <img src="assets/icons/report.png" alt="Patients Icon" width="64" height="64">
                    </div>
                    <h3 class="card-title">Reports</h3>
                    <p class="card-text text-muted">View and manage patient reports.</p>
                    <a href="reports/summary.php" class="btn btn-outline-success btn-lg w-100 stretched-link">
                        Manage Reports
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>