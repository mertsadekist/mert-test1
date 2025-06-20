<?php
// navbar.php
require_once 'auth.php';

// Initialize language session if not set
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ar';
}

// Handle language switch
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = ($_GET['lang'] === 'en') ? 'en' : 'ar';
    // Redirect to remove lang parameter from URL
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

// Translations array
$translations = [
    'ar' => [
        'system_name' => 'نظام إدارة العقارات',
        'home' => 'الرئيسية',
        'upload_excel' => 'رفع ملف إكسل',
        'manage_projects' => 'إدارة المشاريع',
        'view_by_project' => 'عرض حسب المشروع',
        'filter_apartments' => 'تصفية الشقق',
        'register_user' => 'تسجيل مستخدم',
        'logout' => 'تسجيل الخروج',
        'switch_language' => 'English'
    ],
    'en' => [
        'system_name' => 'Real Estate Management System',
        'home' => 'Home',
        'upload_excel' => 'Upload Excel',
        'manage_projects' => 'Manage Projects',
        'view_by_project' => 'View by Project',
        'filter_apartments' => 'Filter Apartments',
        'register_user' => 'Register User',
        'logout' => 'Logout',
        'switch_language' => 'عربي'
    ]
];

// Current language
$lang = $_SESSION['lang'];
$t = $translations[$lang];

// Set direction based on language
$dir = ($lang === 'ar') ? 'rtl' : 'ltr';
?>

<style>
    body {
        direction: <?php echo $dir; ?>;
    }
    .navbar-nav {
        margin-<?php echo ($lang === 'ar') ? 'right' : 'left'; ?>: auto;
    }
    /* Fix icon spacing based on language */
    .nav-link i {
        margin-<?php echo ($lang === 'ar') ? 'left' : 'right'; ?>: 0.5rem !important;
        margin-<?php echo ($lang === 'ar') ? 'right' : 'left'; ?>: 0 !important;
    }
    /* Adjust navbar brand spacing */
    .navbar-brand img {
        margin-<?php echo ($lang === 'ar') ? 'left' : 'right'; ?>: 0.5rem !important;
        margin-<?php echo ($lang === 'ar') ? 'right' : 'left'; ?>: 0 !important;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="assets/logo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
            <?php echo $t['system_name']; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-home"></i> <?php echo $t['home']; ?>
                    </a>
                </li>
                <?php if (can('upload_apartments')) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="upload_form.php">
                            <i class="fas fa-file-excel"></i> <?php echo $t['upload_excel']; ?>
                        </a>
                    </li>
                <?php } ?>
                <?php if (can('manage_projects')) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_developers_projects.php">
                            <i class="fas fa-building"></i> <?php echo $t['manage_projects']; ?>
                        </a>
                    </li>
                <?php } ?>
                <li class="nav-item">
                    <a class="nav-link" href="display_apartments.php">
                        <i class="fas fa-list"></i> <?php echo $t['view_by_project']; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="all_apartments.php">
                        <i class="fas fa-filter"></i> <?php echo $t['filter_apartments']; ?>
                    </a>
                </li>
                <?php if (can('manage_users')) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="register_user.php">
                            <i class="fas fa-user-plus"></i> <?php echo $t['register_user']; ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="?lang=<?php echo ($lang === 'ar') ? 'en' : 'ar'; ?>">
                        <i class="fas fa-language"></i> <?php echo $t['switch_language']; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
