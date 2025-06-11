<?php
// dashboard.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['user_name'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Real Estate Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="upload_form.php">Upload Excel</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_developers_projects.php">Manage Projects</a></li>
                <li class="nav-item"><a class="nav-link" href="display_apartments.php">View By Project</a></li>
                <li class="nav-item"><a class="nav-link active" href="all_apartments.php">Filter All Apartments</a></li>
                <li class="nav-item"><a class="nav-link" href="register_user.php">Register User</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

    <div class="container mt-4">
        <div class="alert alert-primary" role="alert">
            <h4 class="alert-heading">Welcome, <?= htmlspecialchars($name) ?>!</h4>
            <p>Your role: <strong><?= htmlspecialchars($role) ?></strong></p>
            <hr>
            <p class="mb-0">Use the navigation menu above to manage data and access application features.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
