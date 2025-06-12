<?php
// manage_users.php
require_once 'auth.php';
require_once 'roles.php';
require_role([ROLE_ADMIN]);

require_once 'db_connection.php';
require_once 'csrf.php';
$alert = '';

// Add new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    if (!in_array($role, ROLES, true)) {
        die('Invalid role');
    }

    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $name, $email, $password, $role);
    if ($stmt->execute()) {
        $alert = 'User added successfully!';
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header("Location: manage_users.php?deleted=1");
    exit();
}

// Update password or role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $id = intval($_POST['user_id']);
    $role = $_POST['role'];
    if (!in_array($role, ROLES, true)) {
        die('Invalid role');
    }

    if (!empty($_POST['new_password'])) {
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ?, role = ? WHERE id = ?");
        $stmt->bind_param('ssi', $new_password, $role, $id);
        $stmt->execute();
        $alert = 'Password and role updated successfully!';
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param('si', $role, $id);
        $stmt->execute();
        $alert = 'Role updated successfully!';
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$like = '%' . $search . '%';
$stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY name");
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$users = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Manage Users</h2>

    <?php if ($alert): ?>
        <div class="alert alert-success"><?= $alert ?></div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert alert-warning">User deleted successfully.</div>
    <?php endif; ?>

    <form method="get" class="mb-4 row">
        <div class="col-md-10">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by name or email...">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100">Search</button>
        </div>
    </form>

    <h4>Add New User</h4>
    <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= generate_token() ?>">
        <input type="hidden" name="action" value="add">
        <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
        <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
        <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
        <div class="col-md-2">
            <select name="role" class="form-control">
                <option value="<?= ROLE_VIEWER ?>">Viewer</option>
                <option value="<?= ROLE_EDITOR ?>">Editor</option>
                <option value="<?= ROLE_ADMIN ?>">Admin</option>
            </select>
        </div>
        <div class="col-md-1"><button type="submit" class="btn btn-primary">Add</button></div>
    </form>

    <h4 class="mt-5">Current Users</h4>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Update Role / Password</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <form method="post" class="row g-2">
                            <input type="hidden" name="csrf_token" value="<?= generate_token() ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <div class="col-md-5">
                                <select name="role" class="form-control">
                                    <option value="<?= ROLE_VIEWER ?>" <?= $user['role'] === ROLE_VIEWER ? 'selected' : '' ?>>Viewer</option>
                                    <option value="<?= ROLE_EDITOR ?>" <?= $user['role'] === ROLE_EDITOR ? 'selected' : '' ?>>Editor</option>
                                    <option value="<?= ROLE_ADMIN ?>" <?= $user['role'] === ROLE_ADMIN ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-5"><input type="password" name="new_password" class="form-control" placeholder="New Password"></div>
                            <div class="col-md-2"><button type="submit" class="btn btn-success">Update</button></div>
                        </form>
                    </td>
                    <td>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?delete=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
