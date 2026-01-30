<?php
session_start();

$project_folder = "/Patient Visit & Follow-Up";

$path = $project_folder;
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
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Visit & Follow Up</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-3 p-2">
        <div class="container-fluid">

            <a class="navbar-brand" href="<?php echo $path; ?>/index.php">
                <strong class="text-primary">Patients Manager</strong>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto nav-underline">

                    <li class="nav-item">
                        <a class="nav-link text-dark <?php echo $is_home ? 'active' : ''; ?>"
                            href="<?php echo $path; ?>/index.php">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-dark <?php echo $is_patients ? 'active' : ''; ?>"
                            href="<?php echo $path; ?>/patients/list.php">Patients</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-dark <?php echo $is_visits ? 'active' : ''; ?>"
                            href="<?php echo $path; ?>/visits/list.php">Visits</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-dark <?php echo $is_reports ? 'active' : ''; ?>"
                            href="<?php echo $path; ?>/reports/summary.php">Reports</a>
                    </li>

                </ul>
            </div>

        </div>
    </nav>

    <div class="container">