<?php
// navbar.php
require_once 'auth.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Real Estate Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                  <?php if (can('upload_apartments')) { ?>
                    <li class="nav-item"><a class="nav-link" href="upload_form.php">Upload Excel</a></li>
                <?php } ?>
                <?php if (can('manage_projects')) { ?>
                    <li class="nav-item"><a class="nav-link" href="manage_developers_projects.php">Manage Projects</a></li>
                <?php } ?>
                <li class="nav-item"><a class="nav-link" href="display_apartments.php">View By Project</a></li>
                <li class="nav-item"><a class="nav-link" href="all_apartments.php">Filter All Apartments</a></li>
                <?php if (can('manage_users')) { ?>
                    <li class="nav-item"><a class="nav-link" href="register_user.php">Register User</a></li>
                <?php } ?>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
