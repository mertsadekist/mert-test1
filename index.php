<?php
// index.php (now handles login)
session_start();

require_once 'config.php';
require_once 'db_connection.php';
require_once 'csrf.php';
require_once 'includes/SecurityHelper.php';
require_once 'includes/ErrorHandler.php';

// Initialize error handler
ErrorHandler::initialize();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

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
        'title' => 'نظام إدارة العقارات - تسجيل الدخول',
        'system_name' => 'نظام إدارة العقارات',
        'email' => 'البريد الإلكتروني',
        'email_placeholder' => 'أدخل البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'password_placeholder' => 'أدخل كلمة المرور',
        'login' => 'تسجيل الدخول',
        'switch_language' => 'English',
        'copyright' => 'جميع الحقوق محفوظة',
        'error_invalid_token' => 'رمز CSRF غير صالح',
        'error_too_many_attempts' => 'محاولات كثيرة خاطئة. يرجى المحاولة لاحقاً',
        'error_invalid_password' => 'كلمة المرور غير صحيحة',
        'error_user_not_found' => 'المستخدم غير موجود أو غير نشط',
        'error_system' => 'حدث خطأ في النظام. يرجى المحاولة مرة أخرى لاحقاً'
    ],
    'en' => [
        'title' => 'Real Estate Management System - Login',
        'system_name' => 'Real Estate Management System',
        'email' => 'Email',
        'email_placeholder' => 'Enter your email',
        'password' => 'Password',
        'password_placeholder' => 'Enter your password',
        'login' => 'Login',
        'switch_language' => 'عربي',
        'copyright' => 'All rights reserved',
        'error_invalid_token' => 'Invalid CSRF token',
        'error_too_many_attempts' => 'Too many failed attempts. Please try again later',
        'error_invalid_password' => 'Invalid password',
        'error_user_not_found' => 'User not found or inactive',
        'error_system' => 'A system error occurred. Please try again later'
    ]
];

// Current language
$lang = $_SESSION['lang'];
$t = $translations[$lang];
$dir = ($lang === 'ar') ? 'rtl' : 'ltr';

// Initialize security
$security = SecurityHelper::getInstance();
$security->enforceSSL();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Set session cookie parameters
$lifetime = Config::getInstance()->get('security.session_lifetime', 3600);
session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        die($t['error_invalid_token']);
    }

    $email = $security->sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    // Check for too many login attempts
    if (!$security->checkLoginAttempts($email)) {
        $error = $t['error_too_many_attempts'];
    } else {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND active = 1 LIMIT 1");
            if (!$stmt) {
                throw new Exception("Database preparation failed");
            }
            $stmt->bind_param('s', $email);
            if (!$stmt->execute()) {
                throw new Exception("Query execution failed");
            }
            $result = $stmt->get_result();
            if (!$result) {
                throw new Exception("Failed to get result set");
            }

            if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                // Successful login
                $security->recordLoginAttempt($email, true);
                
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Log successful login
                error_log("Successful login for user: {$email}");
                
                header("Location: dashboard.php");
                exit();
            } else {
                $security->recordLoginAttempt($email, false);
                $error = $t['error_invalid_password'];
                error_log("Failed login attempt for user: {$email}");
            }
        } else {
                $error = $t['error_user_not_found'];
                error_log("Login attempt for non-existent user: {$email}");
            }

            // Free the result set
            $result->free();
            $stmt->close();

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = $t['error_system'];
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 15px;
            position: relative;
            overflow: hidden;
        }
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #007bff, #6610f2);
        }
        .logo {
            display: block;
            margin: 0 auto 2rem;
            width: 120px;
            height: auto;
            transition: transform 0.3s ease;
        }
        .logo:hover {
            transform: scale(1.05);
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
        }
        .btn-primary {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        .error-message {
            background-color: #fff5f5;
            border: 1px solid #feb2b2;
            color: #c53030;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(197, 48, 48, 0.1);
            font-size: 0.95rem;
            line-height: 1.5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-message i {
            margin-inline-end: 0.5rem;
            font-size: 1.1rem;
        }
        .input-group-text {
            background-color: transparent;
            border: 2px solid #e9ecef;
            border-right: none;
        }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="assets/logo.png" alt="شعار الشركة" class="logo">
        <h3 class="mb-4 text-center"><?php echo $t['system_name']; ?></h3>
        
        <?php if (isset($error)): ?>
            <div class="error-message mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="post" class="text-end">
            <input type="hidden" name="csrf_token" value="<?= generate_token() ?>">
            
            <div class="mb-4">
                <label for="email" class="form-label"><?php echo $t['email']; ?></label>
                <div class="input-group">
                    <input type="email" 
                           id="email"
                           name="email" 
                           class="form-control" 
                           placeholder="<?php echo $t['email_placeholder']; ?>" 
                           required
                           autocomplete="email">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label"><?php echo $t['password']; ?></label>
                <div class="input-group">
                    <input type="password" 
                           id="password"
                           name="password" 
                           class="form-control" 
                           placeholder="<?php echo $t['password_placeholder']; ?>" 
                           required
                           autocomplete="current-password">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i>
                <?php echo $t['login']; ?>
            </button>

            <div class="text-center mb-3">
                <a href="?lang=<?php echo ($lang === 'ar') ? 'en' : 'ar'; ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-language me-1"></i>
                    <?php echo $t['switch_language']; ?>
                </a>
            </div>
            
            <div class="text-center text-muted">
                <small><?php echo $t['copyright']; ?> &copy; <?= date('Y') ?></small>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
