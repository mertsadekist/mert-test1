<?php
// display_apartments.php with advanced filter
$host = 'localhost';
$db = 'u891594679_stoks';
$user = 'u891594679_stoks';
$pass = '^1QQHgpeQ7o';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selected_developer = $_POST['developer_id'] ?? '';
$selected_project = $_POST['project_id'] ?? '';
$selected_location = $_POST['location'] ?? '';

$developers = $conn->query("SELECT id, name FROM developers ORDER BY name");
$projects = $selected_developer ? $conn->query("SELECT id, name FROM projects WHERE developer_id = '$selected_developer' ORDER BY name") : [];
$locations = $conn->query("SELECT DISTINCT location FROM projects WHERE location IS NOT NULL AND location != '' ORDER BY location");
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conditions = [];
    if (!empty($selected_project)) {
        $conditions[] = "project_id = '$selected_project'";
    } elseif (!empty($selected_location)) {
        $location_safe = $conn->real_escape_string($selected_location);
        $project_ids_query = $conn->query("SELECT id FROM projects WHERE location = '$location_safe'");
        $ids = [];
        while ($row = $project_ids_query->fetch_assoc()) {
            $ids[] = "'{$row['id']}'";
        }
        if ($ids) {
            $conditions[] = "project_id IN (" . implode(",", $ids) . ")";
        }
    }
    $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
    $query = "SELECT * FROM apartments $where ORDER BY floor, unit_number";
    $result = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Apartments</title>
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

<div class="container mt-5">
    <h2 class="mb-4">Filter Apartments</h2>
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Developer</label>
            <select name="developer_id" class="form-select" onchange="this.form.submit()">
                <option value="">-- Select Developer --</option>
                <?php if ($developers) while ($dev = $developers->fetch_assoc()): ?>
                    <option value="<?= $dev['id'] ?>" <?= $selected_developer === $dev['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dev['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Project</label>
            <select name="project_id" class="form-select">
                <option value="">-- Select Project --</option>
                <?php if ($projects && $projects instanceof mysqli_result) while ($p = $projects->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>" <?= $selected_project === $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Location</label>
            <select name="location" class="form-select">
                <option value="">-- Select Location --</option>
                <?php if ($locations) while ($loc = $locations->fetch_assoc()): ?>
                    <option value="<?= $loc['location'] ?>" <?= $selected_location === $loc['location'] ? 'selected' : '' ?>><?= htmlspecialchars($loc['location']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <?php if ($result): ?>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Unit Number</th>
                        <th>Floor</th>
                        <th>Bedrooms</th>
                        <th>Bathrooms</th>
                        <th>Area (sqm)</th>
                        <th>Price</th>
                        <th>Payment Type</th>
                        <th>Cash Discount (%)</th>
                        <th>Installment Plan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['unit_number']) ?></td>
                            <td><?= (int)$row['floor'] ?></td>
                            <td><?= (int)$row['bedrooms'] ?></td>
                            <td><?= (int)$row['bathrooms'] ?></td>
                            <td><?= number_format($row['area_sqm'], 2) ?></td>
                            <td><?= number_format($row['price'], 2) ?></td>
                            <td><?= htmlspecialchars($row['payment_type']) ?></td>
                            <td><?= number_format($row['cash_discount'], 2) ?></td>
                            <td><?= htmlspecialchars($row['installment_plan']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning">No apartments found matching the filter.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
