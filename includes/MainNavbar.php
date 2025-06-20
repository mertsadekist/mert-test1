<?php
// MainNavbar.php - Unified Navigation Menu
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
        'manage_projects' => 'إدارة المشاريع والمطورين',
        'view_by_project' => 'عرض حسب المشروع',
        'filter_apartments' => 'تصفية جميع الشقق',
        'manage_users' => 'إدارة المستخدمين',
        'register_user' => 'تسجيل مستخدم',
        'export_excel' => 'تصدير إكسل',
        'export_pdf' => 'تصدير PDF',
        'error_logs' => 'سجل الأخطاء',
        'logout' => 'تسجيل الخروج',
        'switch_language' => 'English',
        'profile' => 'الملف الشخصي',
        'settings' => 'الإعدادات',
        'reports' => 'التقارير'
    ],
    'en' => [
        'system_name' => 'Real Estate Management System',
        'home' => 'Home',
        'upload_excel' => 'Upload Excel',
        'manage_projects' => 'Manage Projects & Developers',
        'view_by_project' => 'View by Project',
        'filter_apartments' => 'Filter All Apartments',
        'manage_users' => 'Manage Users',
        'register_user' => 'Register User',
        'export_excel' => 'Export Excel',
        'export_pdf' => 'Export PDF',
        'error_logs' => 'Error Logs',
        'logout' => 'Logout',
        'switch_language' => 'عربي',
        'profile' => 'Profile',
        'settings' => 'Settings',
        'reports' => 'Reports'
    ]
];

// Current language
$lang = $_SESSION['lang'];
$t = $translations[$lang];

// Set direction based on language
$dir = ($lang === 'ar') ? 'rtl' : 'ltr';

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

function isActivePage($page) {
    global $current_page;
    return ($current_page === $page) ? 'active' : '';
}
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
    /* Dropdown menu styling */
    .dropdown-menu {
        direction: <?php echo $dir; ?>;
    }
    .navbar-brand {
        font-weight: bold;
        font-size: 1.2rem;
    }
    .nav-link.active {
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 0.375rem;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <img src="assets/logo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top">
            <?php echo $t['system_name']; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Home -->
                <li class="nav-item">
                    <a class="nav-link <?php echo isActivePage('dashboard.php'); ?>" href="dashboard.php">
                        <i class="fas fa-home"></i> <?php echo $t['home']; ?>
                    </a>
                </li>
                
                <!-- Upload Excel (if user has permission) -->
                <?php if (can('upload_apartments')) { ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActivePage('upload_form.php'); ?>" href="upload_form.php">
                            <i class="fas fa-file-excel"></i> <?php echo $t['upload_excel']; ?>
                        </a>
                    </li>
                <?php } ?>
                
                <!-- Manage Projects (if user has permission) -->
                <?php if (can('manage_projects')) { ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActivePage('manage_developers_projects.php'); ?>" href="manage_developers_projects.php">
                            <i class="fas fa-building"></i> <?php echo $t['manage_projects']; ?>
                        </a>
                    </li>
                <?php } ?>
                
                <!-- View Apartments -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="apartmentsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-list"></i> <?php echo $t['view_by_project']; ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="apartmentsDropdown">
                        <li><a class="dropdown-item <?php echo isActivePage('display_apartments.php'); ?>" href="display_apartments.php">
                            <i class="fas fa-eye"></i> <?php echo $t['view_by_project']; ?>
                        </a></li>
                        <li><a class="dropdown-item <?php echo isActivePage('all_apartments.php'); ?>" href="all_apartments.php">
                            <i class="fas fa-filter"></i> <?php echo $t['filter_apartments']; ?>
                        </a></li>
                    </ul>
                </li>
                
                <!-- Export Options -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="exportDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> <?php echo $t['reports']; ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="export_excel.php">
                            <i class="fas fa-file-excel"></i> <?php echo $t['export_excel']; ?>
                        </a></li>
                        <li><a class="dropdown-item" href="export_pdf.php">
                            <i class="fas fa-file-pdf"></i> <?php echo $t['export_pdf']; ?>
                        </a></li>
                    </ul>
                </li>
                
                <!-- User Management (if user has permission) -->
                <?php if (can('manage_users')) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-users"></i> <?php echo $t['manage_users']; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="usersDropdown">
                            <li><a class="dropdown-item <?php echo isActivePage('manage_users.php'); ?>" href="manage_users.php">
                                <i class="fas fa-users-cog"></i> <?php echo $t['manage_users']; ?>
                            </a></li>
                            <li><a class="dropdown-item <?php echo isActivePage('register_user.php'); ?>" href="register_user.php">
                                <i class="fas fa-user-plus"></i> <?php echo $t['register_user']; ?>
                            </a></li>
                        </ul>
                    </li>
                    
                    <!-- Error Logs (Admin only) -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActivePage('error_logs.php'); ?>" href="error_logs.php">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $t['error_logs']; ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
            
            <!-- Right side menu -->
            <ul class="navbar-nav">
                <!-- Language Switch -->
                <li class="nav-item">
                    <a class="nav-link" href="?lang=<?php echo ($lang === 'ar') ? 'en' : 'ar'; ?>" title="<?php echo $t['switch_language']; ?>">
                        <i class="fas fa-language"></i> <?php echo $t['switch_language']; ?>
                    </a>
                </li>
                
                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#">
                            <i class="fas fa-user-circle"></i> <?php echo $t['profile']; ?>
                        </a></li>
                        <li><a class="dropdown-item" href="#">
                            <i class="fas fa-cog"></i> <?php echo $t['settings']; ?>
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> <?php echo $t['logout']; ?>
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Add some spacing after navbar -->
<div class="mb-4"></div>