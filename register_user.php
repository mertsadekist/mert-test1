<?php
// register_user.php
require_once 'auth.php';
require_capability('manage_users');

require_once 'db_connection.php';
require_once 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $conn->real_escape_string($_POST['role']);
    if (!in_array($role, ROLES, true)) {
        die('Invalid role');
    }
    $id = uniqid();

    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $error = "A user with this email already exists.";
    } else {
        $sql = "INSERT INTO users (id, name, email, password_hash, role) VALUES ('$id', '$name', '$email', '$password', '$role')";
        if ($conn->query($sql)) {
            $success = "User registered successfully.";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register New User</title>
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
                    <li class="nav-item"><a class="nav-link" href="display_apartments.php">View Apartments</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Register User</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Register New User (Admin Only)</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        <form method="post" class="mt-4">
             <input type="hidden" name="csrf_token" value="<?= generate_token() ?>">
            <div class="mb-3">
                <label class="form-label">Name:</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password:</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role:</label>
                <select name="role" class="form-select" required>
                    <option value="<?= ROLE_VIEWER ?>">Viewer</option>
                    <option value="<?= ROLE_EDITOR ?>">Editor</option>
                    <option value="<?= ROLE_ADMIN ?>">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Register User</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
