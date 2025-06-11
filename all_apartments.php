<?php
// all_apartments.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connection.php';

$filters = [];
$where = [];

if (!empty($_GET['developer_id'])) {
    $filters['developer_id'] = $_GET['developer_id'];
    $where[] = "projects.developer_id = '" . $conn->real_escape_string($_GET['developer_id']) . "'";
}
if (!empty($_GET['project_id'])) {
    $filters['project_id'] = $_GET['project_id'];
    $where[] = "apartments.project_id = '" . $conn->real_escape_string($_GET['project_id']) . "'";
}
if (!empty($_GET['location'])) {
    $filters['location'] = $_GET['location'];
    $where[] = "projects.location LIKE '%" . $conn->real_escape_string($_GET['location']) . "%'";
}
if (!empty($_GET['bedrooms'])) {
    $filters['bedrooms'] = $_GET['bedrooms'];
    $where[] = "apartments.bedrooms = '" . intval($_GET['bedrooms']) . "'";
}
if (!empty($_GET['area_min'])) {
    $filters['area_min'] = $_GET['area_min'];
    $where[] = "apartments.area_sqm >= '" . floatval($_GET['area_min']) . "'";
}
if (!empty($_GET['area_max'])) {
    $filters['area_max'] = $_GET['area_max'];
    $where[] = "apartments.area_sqm <= '" . floatval($_GET['area_max']) . "'";
}
if (!empty($_GET['price_min'])) {
    $filters['price_min'] = $_GET['price_min'];
    $where[] = "apartments.price >= '" . floatval($_GET['price_min']) . "'";
}
if (!empty($_GET['price_max'])) {
    $filters['price_max'] = $_GET['price_max'];
    $where[] = "apartments.price <= '" . floatval($_GET['price_max']) . "'";
}

$where_clause = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT apartments.*, projects.name AS project_name, developers.name AS developer_name FROM apartments 
        JOIN projects ON apartments.project_id = projects.id 
        JOIN developers ON projects.developer_id = developers.id 
        $where_clause 
        ORDER BY apartments.id DESC";
$result = $conn->query($sql);

$developers = $conn->query("SELECT id, name FROM developers ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Apartments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function fetchProjects(developerId) {
            fetch('get_projects.php?developer_id=' + developerId)
                .then(response => response.json())
                .then(data => {
                    const projectSelect = document.getElementById('project_id');
                    projectSelect.innerHTML = '<option value="">-- Project --</option>';
                    data.forEach(project => {
                        const option = document.createElement('option');
                        option.value = project.id;
                        option.textContent = project.name;
                        projectSelect.appendChild(option);
                    });
                });
        }
    </script>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>All Apartments</h2>
    <form method="get" class="row g-3">
        <div class="col-md-3">
            <select name="developer_id" class="form-control" onchange="fetchProjects(this.value)">
                <option value="">-- Developer --</option>
                <?php if ($developers): while($dev = $developers->fetch_assoc()): ?>
                    <option value="<?= $dev['id'] ?>" <?= (isset($_GET['developer_id']) && $_GET['developer_id'] == $dev['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dev['name']) ?>
                    </option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="project_id" class="form-control" id="project_id">
                <option value="">-- Project --</option>
            </select>
        </div>
        <div class="col-md-2"><input type="text" name="location" class="form-control" placeholder="Location" value="<?= $_GET['location'] ?? '' ?>"></div>
        <div class="col-md-1"><input type="number" name="bedrooms" class="form-control" placeholder="Bedrooms" value="<?= $_GET['bedrooms'] ?? '' ?>"></div>
        <div class="col-md-2"><input type="number" step="0.01" name="area_min" class="form-control" placeholder="Area Min" value="<?= $_GET['area_min'] ?? '' ?>"></div>
        <div class="col-md-2"><input type="number" step="0.01" name="area_max" class="form-control" placeholder="Area Max" value="<?= $_GET['area_max'] ?? '' ?>"></div>
        <div class="col-md-2"><input type="number" step="0.01" name="price_min" class="form-control" placeholder="Price Min" value="<?= $_GET['price_min'] ?? '' ?>"></div>
        <div class="col-md-2"><input type="number" step="0.01" name="price_max" class="form-control" placeholder="Price Max" value="<?= $_GET['price_max'] ?? '' ?>"></div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="all_apartments.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="mt-4">
        <a href="export_excel.php?<?= http_build_query($_GET) ?>" class="btn btn-success">Download Excel</a>
        <a href="export_pdf.php?<?= http_build_query($_GET) ?>" class="btn btn-danger">Download PDF</a>
    </div>

    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Developer</th>
                <th>Project</th>
                <th>Unit</th>
                <th>Floor</th>
                <th>Bedrooms</th>
                <th>Bathrooms</th>
                <th>Area (sqm)</th>
                <th>Price</th>
                <th>Payment Type</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['developer_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['project_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['unit_number'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['floor'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['bedrooms'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['bathrooms'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['area_sqm'] ?? '') ?></td>
                        <td><?= htmlspecialchars(number_format($row['price'], 2)) ?></td>
                        <td><?= htmlspecialchars($row['payment_type'] ?? '') ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center">No apartments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
