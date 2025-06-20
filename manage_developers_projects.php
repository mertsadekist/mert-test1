<?php
require_once 'auth.php';
require_capability('manage_projects');
// manage_developers_projects.php

require_once 'db_connection.php';
require_once 'csrf.php';

// Add Developer
if (isset($_POST['add_developer'])) {
        if (!verify_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $dev_name = $_POST['developer_name'];
    $dev_id = uniqid();
    $stmt = $conn->prepare("INSERT INTO developers (id, name) VALUES (?, ?)");
    $stmt->bind_param('ss', $dev_id, $dev_name);
    $stmt->execute();
}

// Add Project
if (isset($_POST['add_project'])) {
        if (!verify_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $project_name = $_POST['project_name'];
    $location = $_POST['location'];
    $developer_id = $_POST['developer_id'];
    $project_id = uniqid();
     $stmt = $conn->prepare("INSERT INTO projects (id, developer_id, name, location) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $project_id, $developer_id, $project_name, $location);
    $stmt->execute();
}

// Fetch all developers
$developers = $conn->query("SELECT * FROM developers ORDER BY name ASC");
// Fetch all projects with developer names
$projects = $conn->query("SELECT p.*, d.name as developer_name FROM projects p JOIN developers d ON p.developer_id = d.id ORDER BY d.name, p.name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Developers & Projects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/MainNavbar.php'; ?>

    <div class="container mt-5">
        <h2>Add New Developer</h2>
        <form method="post" class="mb-5">
            <input type="hidden" name="csrf_token" value="<?= generate_token() ?>">
            <div class="mb-3">
                <input type="text" name="developer_name" class="form-control" placeholder="Developer Name" required>
            </div>
            <button type="submit" name="add_developer" class="btn btn-primary">Add Developer</button>
        </form>

        <h2>Add New Project</h2>
        <form method="post" class="mb-5">
            <input type="hidden" name="csrf_token" value="<?= generate_token() ?>">
            <div class="mb-3">
                <input type="text" name="project_name" class="form-control" placeholder="Project Name" required>
            </div>
            <div class="mb-3">
                <input type="text" name="location" class="form-control" placeholder="Location" required>
            </div>
            <div class="mb-3">
                <select name="developer_id" class="form-select" required>
                    <option value="">Select Developer</option>
                    <?php $developers->data_seek(0); while($dev = $developers->fetch_assoc()) { ?>
                        <option value="<?= $dev['id'] ?>"><?= $dev['name'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" name="add_project" class="btn btn-success">Add Project</button>
        </form>

        <h2>All Projects</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Project Name</th>
                    <th>Location</th>
                    <th>Developer</th>
                    <th>Project ID</th>
                </tr>
            </thead>
            <tbody>
                <?php while($proj = $projects->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $proj['name'] ?></td>
                        <td><?= $proj['location'] ?></td>
                        <td><?= $proj['developer_name'] ?></td>
                        <td><?= $proj['id'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
