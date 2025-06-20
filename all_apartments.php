<?php
// all_apartments.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db_connection.php';

$filters = [];
$whereParts = [];
$params = [];
$types = '';

if (!empty($_GET['developer_id'])) {
    $filters['developer_id'] = $_GET['developer_id'];
    $whereParts[] = "projects.developer_id = ?";
    $types .= 's';
    $params[] = $_GET['developer_id'];
}
if (!empty($_GET['project_id'])) {
    $filters['project_id'] = $_GET['project_id'];
    $whereParts[] = "apartments.project_id = ?";
    $types .= 's';
    $params[] = $_GET['project_id'];
}
if (!empty($_GET['location'])) {
    $filters['location'] = $_GET['location'];
    $whereParts[] = "projects.location LIKE ?";
    $types .= 's';
    $params[] = '%' . $_GET['location'] . '%';
}
if (!empty($_GET['bedrooms'])) {
    $filters['bedrooms'] = $_GET['bedrooms'];
    $whereParts[] = "apartments.bedrooms = ?";
    $types .= 'i';
    $params[] = (int)$_GET['bedrooms'];
}
if (!empty($_GET['area_min'])) {
    $filters['area_min'] = $_GET['area_min'];
    $whereParts[] = "apartments.area_sqm >= ?";
    $types .= 'd';
    $params[] = (float)$_GET['area_min'];
}
if (!empty($_GET['area_max'])) {
    $filters['area_max'] = $_GET['area_max'];
    $whereParts[] = "apartments.area_sqm <= ?";
    $types .= 'd';
    $params[] = (float)$_GET['area_max'];
}
if (!empty($_GET['price_min'])) {
    $filters['price_min'] = $_GET['price_min'];
    $whereParts[] = "apartments.price >= ?";
    $types .= 'd';
    $params[] = (float)$_GET['price_min'];
}
if (!empty($_GET['price_max'])) {
    $filters['price_max'] = $_GET['price_max'];
    $whereParts[] = "apartments.price <= ?";
    $types .= 'd';
    $params[] = (float)$_GET['price_max'];
}

$where_clause = $whereParts ? "WHERE " . implode(" AND ", $whereParts) : "";

$sql = "SELECT apartments.*, projects.name AS project_name, developers.name AS developer_name FROM apartments 
        JOIN projects ON apartments.project_id = projects.id 
        JOIN developers ON projects.developer_id = developers.id 
        $where_clause 
        ORDER BY apartments.id DESC";
$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$developers = $conn->query("SELECT id, name FROM developers ORDER BY name");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الشقق</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            direction: rtl;
            text-align: right;
        }

        .filter-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .filter-card .card-header {
            background: #f8f9fa;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }

        .filter-card .card-body {
            padding: 2rem;
        }

        .filter-form label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .filter-form .form-control,
        .filter-form .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem;
        }

        .apartment-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .apartment-card:hover {
            transform: translateY(-5px);
        }

        .apartment-card .card-header {
            border-radius: 15px 15px 0 0;
            padding: 1rem;
        }

        .apartment-details .detail-item {
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .apartment-details .detail-item:last-child {
            border-bottom: none;
        }

        .apartment-details .detail-item i {
            color: #0d6efd;
            width: 20px;
        }

        .apartment-details .price {
            font-size: 1.1rem;
            font-weight: bold;
            color: #198754;
        }

        .btn-group .btn {
            border-radius: 8px;
            margin-right: 0.5rem;
        }

        .alert {
            border-radius: 10px;
            padding: 1rem;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const selectedDeveloperId = urlParams.get('developer_id');
            const selectedProjectId = urlParams.get('project_id');

            if (selectedDeveloperId) {
                fetchProjects(selectedDeveloperId, selectedProjectId);
            }
        });

        async function fetchProjects(developerId, selectedProjectId = '') {
            const projectSelect = document.getElementById('project_id');
            
            if (!developerId) {
                projectSelect.innerHTML = '<option value="">-- اختر المشروع --</option>';
                return;
            }

            projectSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            projectSelect.disabled = true;

            try {
                const response = await fetch('get_projects.php?developer_id=' + developerId);
                if (!response.ok) throw new Error('فشل في جلب البيانات');
                
                const projects = await response.json();
                let options = '<option value="">-- اختر المشروع --</option>';
                
                projects.forEach(project => {
                    options += `<option value="${project.id}" ${project.id == selectedProjectId ? 'selected' : ''}>
                        ${project.name}
                    </option>`;
                });

                projectSelect.innerHTML = options;
            } catch (error) {
                console.error('خطأ:', error);
                projectSelect.innerHTML = '<option value="">حدث خطأ في جلب المشاريع</option>';
            } finally {
                projectSelect.disabled = false;
            }
        }

        document.querySelector('.filter-form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري البحث...';
        });
    </script>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <div class="filter-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="fas fa-filter me-2"></i>تصفية الشقق</h2>
            <div>
                <a href="export_excel.php?<?= http_build_query($_GET) ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> تصدير Excel
                </a>
                <a href="export_pdf.php?<?= http_build_query($_GET) ?>" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf me-1"></i> تصدير PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" class="filter-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">المطور</label>
                        <select name="developer_id" class="form-select" onchange="fetchProjects(this.value)">
                            <option value="">-- اختر المطور --</option>
                            <?php if ($developers): while($dev = $developers->fetch_assoc()): ?>
                                <option value="<?= $dev['id'] ?>" <?= (isset($_GET['developer_id']) && $_GET['developer_id'] == $dev['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dev['name']) ?>
                                </option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">المشروع</label>
                        <select name="project_id" class="form-select" id="project_id">
                            <option value="">-- اختر المشروع --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">الموقع</label>
                        <input type="text" name="location" class="form-control" placeholder="أدخل الموقع" value="<?= $_GET['location'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">عدد الغرف</label>
                        <input type="number" name="bedrooms" class="form-control" placeholder="عدد الغرف" value="<?= $_GET['bedrooms'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">المساحة (متر مربع)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="area_min" class="form-control" placeholder="من" value="<?= $_GET['area_min'] ?? '' ?>">
                            <input type="number" step="0.01" name="area_max" class="form-control" placeholder="إلى" value="<?= $_GET['area_max'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">السعر</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="price_min" class="form-control" placeholder="من" value="<?= $_GET['price_min'] ?? '' ?>">
                            <input type="number" step="0.01" name="price_max" class="form-control" placeholder="إلى" value="<?= $_GET['price_max'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="d-grid gap-2 w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> بحث
                            </button>
                            <a href="all_apartments.php" class="btn btn-secondary">
                                <i class="fas fa-redo me-1"></i> إعادة تعيين
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card apartment-card h-100">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= htmlspecialchars($row['project_name'] ?? '') ?></h5>
                            <span class="badge bg-light text-dark"><?= htmlspecialchars($row['payment_type'] ?? '') ?></span>
                        </div>
                        <div class="card-body">
                            <div class="apartment-details">
                                <div class="detail-item">
                                    <i class="fas fa-building me-2"></i>
                                    <span>المطور: <?= htmlspecialchars($row['developer_name'] ?? '') ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-door-open me-2"></i>
                                    <span>رقم الوحدة: <?= htmlspecialchars($row['unit_number'] ?? '') ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-stairs me-2"></i>
                                    <span>الطابق: <?= htmlspecialchars($row['floor'] ?? '') ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-bed me-2"></i>
                                    <span>عدد الغرف: <?= htmlspecialchars($row['bedrooms'] ?? '') ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-bath me-2"></i>
                                    <span>عدد الحمامات: <?= htmlspecialchars($row['bathrooms'] ?? '') ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-ruler-combined me-2"></i>
                                    <span>المساحة: <?= htmlspecialchars($row['area_sqm'] ?? '') ?> متر مربع</span>
                                </div>
                                <div class="detail-item price">
                                    <i class="fas fa-tag me-2"></i>
                                    <span>السعر: <?= number_format($row['price'], 2) ?> ريال</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> لم يتم العثور على شقق.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
