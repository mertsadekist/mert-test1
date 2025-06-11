<?php
// upload_form.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connection.php';
$developers = $conn->query("SELECT id, name FROM developers ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Excel File</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function fetchProjects(developerId) {
            fetch('get_projects.php?developer_id=' + developerId)
                .then(response => response.json())
                .then(data => {
                    const projectSelect = document.getElementById('project_id');
                    projectSelect.innerHTML = '<option value="">-- Select Project --</option>';
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
    <h2>Upload Excel File</h2>
    <form action="process_upload.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="developer_id" class="form-label">Developer:</label>
            <select name="developer_id" id="developer_id" class="form-control" required onchange="fetchProjects(this.value)">
                <option value="">-- Select Developer --</option>
                <?php while ($dev = $developers->fetch_assoc()): ?>
                    <option value="<?= $dev['id'] ?>"><?= htmlspecialchars($dev['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="project_id" class="form-label">Project:</label>
            <select name="project_id" id="project_id" class="form-control" required>
                <option value="">-- Select Project --</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="excel_file" class="form-label">Excel File:</label>
            <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xls,.xlsx" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
