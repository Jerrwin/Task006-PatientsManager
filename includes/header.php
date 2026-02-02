<?php

require_once __DIR__ . '/auth_check.php';

$path = (basename($_SERVER['PHP_SELF']) == 'index.php' || basename($_SERVER['PHP_SELF']) == 'login.php') ? '.' : '..';

$uri = $_SERVER['PHP_SELF'];
$is_home = (basename($uri) == 'index.php');
$is_patients = (strpos($uri, '/patients/') !== false);
$is_reports = (strpos($uri, '/reports/') !== false);
$is_visits = (strpos($uri, '/visits/') !== false);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Visit & Follow Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body class="bg-light d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid">

            <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo $path; ?>/index.php">
                <strong class="text-primary">MediTrack</strong>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto nav-underline">
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark <?php echo $is_home ? 'active fw-bold' : ''; ?>"
                               href="<?php echo $path; ?>/index.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark <?php echo $is_patients ? 'active fw-bold' : ''; ?>"
                               href="<?php echo $path; ?>/patients/list.php">Patients</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark <?php echo $is_visits ? 'active fw-bold' : ''; ?>"
                               href="<?php echo $path; ?>/visits/list.php">Visits</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark <?php echo $is_reports ? 'active fw-bold' : ''; ?>"
                               href="<?php echo $path; ?>/reports/summary.php">Reports</a>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'patient'): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark active fw-bold" href="<?php echo $path; ?>/patient_dashboard.php">My Dashboard</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        
                        <div class="text-end d-none d-lg-block">
                            <small class="d-block text-muted" style="line-height: 1;">Welcome,</small>
                            <span class="fw-bold">
                                <?php echo htmlspecialchars($_SESSION['name'] ?? $_SESSION['username']); ?>
                            </span>
                        </div>
                        
                        <a href="<?php echo $path; ?>/logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
                    
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </nav>

    <div class="container">