<?php
// upload_form.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db_connection.php';
require_once 'csrf.php';

// Initialize language session if not set
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ar';
}

// Translations array
$translations = [
    'ar' => [
        'page_title' => 'رفع ملف إكسل',
        'developer' => 'المطور',
        'select_developer' => '-- اختر المطور --',
        'project' => 'المشروع',
        'select_project' => '-- اختر المشروع --',
        'loading_projects' => 'جاري تحميل المشاريع...',
        'excel_file' => 'ملف إكسل',
        'upload_button' => 'رفع الملف',
        'error_loading' => 'حدث خطأ في تحميل المشاريع'
    ],
    'en' => [
        'page_title' => 'Upload Excel File',
        'developer' => 'Developer',
        'select_developer' => '-- Select Developer --',
        'project' => 'Project',
        'select_project' => '-- Select Project --',
        'loading_projects' => 'Loading projects...',
        'excel_file' => 'Excel File',
        'upload_button' => 'Upload',
        'error_loading' => 'Error loading projects'
    ]
];

// Current language
$lang = $_SESSION['lang'];
$t = $translations[$lang];

// Set direction based on language
$dir = ($lang === 'ar') ? 'rtl' : 'ltr';

$developers = $conn->query("SELECT id, name FROM developers ORDER BY name");
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $t['page_title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            direction: <?php echo $dir; ?>;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .upload-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem;
        }
        .btn-upload {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
        }
    </style>
    <script>
        function fetchProjects(developerId) {
            const projectSelect = document.getElementById('project_id');
            projectSelect.disabled = true;
            projectSelect.innerHTML = '<option value="">' + 
                (document.documentElement.lang === 'ar' ? 'جاري تحميل المشاريع...' : 'Loading projects...') + 
                '</option>';

            fetch('get_projects.php?developer_id=' + developerId)
                .then(response => response.json())
                .then(data => {
                    const defaultText = document.documentElement.lang === 'ar' ? '-- اختر المشروع --' : '-- Select Project --';
                    projectSelect.innerHTML = `<option value="">${defaultText}</option>`;
                    data.forEach(project => {
                        const option = document.createElement('option');
                        option.value = project.id;
                        option.textContent = project.name;
                        projectSelect.appendChild(option);
                    })
                .catch(error => {
                    console.error('Error:', error);
                    const errorText = document.documentElement.lang === 'ar' ? 'حدث خطأ في تحميل المشاريع' : 'Error loading projects';
                    projectSelect.innerHTML = `<option value="">${errorText}</option>`;
                    projectSelect.disabled = false;
                });
                    projectSelect.disabled = false;
                });
        }
    </script>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <div class="upload-form">
        <h2 class="mb-4">
            <i class="fas fa-file-excel me-2"></i>
            <?php echo $t['page_title']; ?>
        </h2>
        <form action="process_upload.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= generate_token() ?>">
            <div class="mb-3">
                <label for="developer_id" class="form-label"><?php echo $t['developer']; ?>:</label>
                <select name="developer_id" id="developer_id" class="form-select" required onchange="fetchProjects(this.value)">
                    <option value=""><?php echo $t['select_developer']; ?></option>
                    <?php while ($dev = $developers->fetch_assoc()): ?>
                        <option value="<?= $dev['id'] ?>"><?= htmlspecialchars($dev['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="project_id" class="form-label"><?php echo $t['project']; ?>:</label>
                <select name="project_id" id="project_id" class="form-select" required>
                    <option value=""><?php echo $t['select_project']; ?></option>
                </select>
            </div>
            <div class="mb-4">
                <label for="excel_file" class="form-label"><?php echo $t['excel_file']; ?>:</label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xls,.xlsx" required>
            </div>
            <button type="submit" class="btn btn-primary btn-upload">
                <i class="fas fa-upload me-2"></i>
                <?php echo $t['upload_button']; ?>
            </button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
