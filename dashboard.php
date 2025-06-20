<?php
// dashboard.php
session_start();
require_once 'config.php';
require_once 'db_connection.php';
require_once 'includes/ErrorHandler.php';

// Initialize error handler
ErrorHandler::initialize();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$name = $_SESSION['user_name'];
$role = $_SESSION['role'];

// Get database connection
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

// Get statistics from database
$stats = [
    'projects' => 0,
    'apartments' => 0,
    'developers' => 0,
    'users' => 0
];

    // Count projects
    $query = "SELECT COUNT(*) as count FROM projects";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        throw new Exception("خطأ في استعلام المشاريع: " . mysqli_error($conn));
    }
    $row = mysqli_fetch_assoc($result);
    if (!$row) {
        throw new Exception("خطأ في قراءة بيانات المشاريع");
    }
    $stats['projects'] = $row['count'];
    mysqli_free_result($result);

    // Count apartments
    $query = "SELECT COUNT(*) as count FROM apartments";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        throw new Exception("خطأ في استعلام الشقق: " . mysqli_error($conn));
    }
    $row = mysqli_fetch_assoc($result);
    if (!$row) {
        throw new Exception("خطأ في قراءة بيانات الشقق");
    }
    $stats['apartments'] = $row['count'];
    mysqli_free_result($result);

    // Count developers
    $query = "SELECT COUNT(*) as count FROM developers";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        throw new Exception("خطأ في استعلام المطورين: " . mysqli_error($conn));
    }
    $row = mysqli_fetch_assoc($result);
    if (!$row) {
        throw new Exception("خطأ في قراءة بيانات المطورين");
    }
    $stats['developers'] = $row['count'];
    mysqli_free_result($result);

    // Count users
    $query = "SELECT COUNT(*) as count FROM users";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        throw new Exception("خطأ في استعلام المستخدمين: " . mysqli_error($conn));
    }
    $row = mysqli_fetch_assoc($result);
    if (!$row) {
        throw new Exception("خطأ في قراءة بيانات المستخدمين");
    }
    $stats['users'] = $row['count'];
    mysqli_free_result($result);

    // Get recent activities
    $activities = [];
       $activitiesError = null;

    // Check if the activities table exists
    $tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'activities'");
    if ($tableCheck && mysqli_num_rows($tableCheck) > 0) {
        mysqli_free_result($tableCheck);

        $query = "SELECT activity_type, description, created_at
                 FROM activities
                 ORDER BY created_at DESC LIMIT 3";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            throw new Exception("خطأ في استعلام النشاطات: " . mysqli_error($conn));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$row) {
                break;
            }
        mysqli_free_result($result);
    } else {
        if ($tableCheck) {
            mysqli_free_result($tableCheck);
        }
        $activitiesError = 'جدول النشاطات غير موجود.';
        }
        $activities[] = $row;
    }
    mysqli_free_result($result);
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $_SESSION['error'] = "حدث خطأ في النظام. يرجى المحاولة مرة أخرى لاحقاً.";
} finally {
    // Close the database connection
    if (isset($conn)) {
        mysqli_close($conn);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            transition: transform 0.2s;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-body {
            padding: 1.5rem;
        }
        .list-group-item {
            transition: all 0.2s;
            border: none;
            border-radius: 10px !important;
            margin-bottom: 0.5rem;
        }
        .list-group-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        .alert {
            border-radius: 15px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .alert-danger {
            background-color: #fff5f5;
            color: #c53030;
            border-left: 4px solid #c53030;
        }
        .alert-primary {
            background-color: #ebf8ff;
            color: #2b6cb0;
            border-left: 4px solid #2b6cb0;
        }
        .alert .btn-close {
            padding: 1.25rem;
            opacity: 0.75;
        }
        .alert .btn-close:hover {
            opacity: 1;
        }
        .display-4 {
            font-weight: 600;
            font-size: 2.5rem;
        }
        .card-title {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<?php include 'includes/MainNavbar.php'; ?>

    <div class="container mt-4">
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div>
                    <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                    ?>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        <div class="alert alert-primary" role="alert">
            <h4 class="alert-heading">مرحباً, <?= htmlspecialchars($name) ?>!</h4>
            <p>دورك: <strong><?= htmlspecialchars($role) ?></strong></p>
            <hr>
            <p class="mb-0">استخدم القائمة أعلاه للوصول إلى ميزات التطبيق</p>
        </div>

        <div class="row mt-4">
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title">المشاريع</h5>
                        <h2 class="display-4 mb-0"><?php echo $stats['projects']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title">الشقق</h5>
                        <h2 class="display-4 mb-0"><?php echo $stats['apartments']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title">المطورين</h5>
                        <h2 class="display-4 mb-0"><?php echo $stats['developers']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <h5 class="card-title">المستخدمين</h5>
                        <h2 class="display-4 mb-0"><?php echo $stats['users']; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">الوصول السريع</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="upload_form.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-upload me-2"></i> رفع ملف إكسل جديد
                            </a>
                            <a href="manage_developers_projects.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-building me-2"></i> إدارة المشاريع
                            </a>
                            <a href="display_apartments.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-home me-2"></i> عرض الشقق حسب المشروع
                            </a>
                            <a href="all_apartments.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-filter me-2"></i> تصفية جميع الشقق
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">آخر النشاطات</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($activities as $activity): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($activity['activity_type']); ?></h6>
                                    <small><?php 
                                        $timestamp = strtotime($activity['created_at']);
                                        $now = time();
                                        $diff = $now - $timestamp;
                                        
                                        if ($diff < 60) {
                                            echo 'منذ ' . $diff . ' ثانية';
                                        } elseif ($diff < 3600) {
                                            echo 'منذ ' . floor($diff/60) . ' دقيقة';
                                        } elseif ($diff < 86400) {
                                            echo 'منذ ' . floor($diff/3600) . ' ساعة';
                                        } else {
                                            echo 'منذ ' . floor($diff/86400) . ' يوم';
                                        }
                                    ?></small>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($activity['description']); ?></p>
                            </div>
                            <?php endforeach; ?>
                            <?php if (isset($activitiesError)): ?>
                            <div class="list-group-item">
                                <p class="mb-1 text-muted"><?php echo htmlspecialchars($activitiesError); ?></p>
                            </div>
                            <?php elseif (empty($activities)): ?>
                            <div class="list-group-item">
                                <p class="mb-1 text-muted">لا توجد نشاطات حديثة</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
