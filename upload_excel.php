<?php
// upload_form.php — Upload Excel with dynamic developer/project selection
$host = 'localhost';
$db = 'u891594679_stoks';
$user = 'u891594679_stoks';
$pass = '^1QQHgpeQ7o';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$developers = $conn->query("SELECT id, name FROM developers ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2>Upload Excel File</h2>
    <form action="process_upload.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="developer_id" class="form-label">Select Developer:</label>
            <select name="developer_id" id="developer_id" class="form-select" required>
                <option value="">-- Select Developer --</option>
                <?php while ($dev = $developers->fetch_assoc()): ?>
                    <option value="<?= $dev['id'] ?>"><?= htmlspecialchars($dev['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="project_id" class="form-label">Select Project:</label>
            <select name="project_id" id="project_id" class="form-select" required>
                <option value="">-- Select Project --</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="excel_file" class="form-label">Select Excel file:</label>
            <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xls,.xlsx" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>

<script>
$(document).ready(function() {
    $('#developer_id').change(function() {
        var devId = $(this).val();
        $('#project_id').html('<option>Loading...</option>');
        if (devId) {
            $.get('get_projects.php', { developer_id: devId }, function(data) {
                $('#project_id').html(data);
            });
        } else {
            $('#project_id').html('<option value="">-- Select Project --</option>');
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
