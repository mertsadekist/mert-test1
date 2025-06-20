<?php
// process_upload.php
require_once 'config.php';
require_once 'auth.php';
require_once 'includes/SecurityHelper.php';
require_once 'includes/SessionManager.php';

// Initialize security and session management
$security = SecurityHelper::getInstance();
$session = SessionManager::getInstance();
$config = Config::getInstance();

// Validate session and permissions
if (!$session->validateSession()) {
    header('Location: index.php');
    exit();
}
require_capability('upload_apartments');

// Only show errors if debug mode is enabled
if ($config->get('app.debug')) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

require_once 'db_connection.php';
require_once 'csrf.php';
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    // Sanitize and validate input
    $developer_id = filter_input(INPUT_POST, 'developer_id', FILTER_VALIDATE_INT);
    $project_id = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);
    $user_id = $session->getSessionValue('user_id');
    $user_name = $session->getSessionValue('user_name', 'unknown');

    // Validate file upload
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        die("<div class='alert alert-danger'>❌ File upload failed.</div>");
    }

    // Validate file using SecurityHelper
    if (!$security->validateFileUpload($_FILES['excel_file'], ['xlsx', 'xls'])) {
        die("<div class='alert alert-danger'>❌ Invalid file type or size.</div>");
    }

    // Generate secure filename
    $original_filename = $_FILES['excel_file']['name'];
    $secure_filename = $security->generateSecureFilename($original_filename);

    echo "<html><head><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'></head><body>";
    include 'includes/MainNavbar.php';

    if (!$developer_id || !$project_id || !isset($_FILES['excel_file'])) {
        die("<div class='alert alert-danger'>❌ Missing required input.</div></div></body></html>");
    }

    $check_project = $conn->prepare("SELECT id FROM projects WHERE id = ?");
    $check_project->bind_param("s", $project_id);
    $check_project->execute();
    $result = $check_project->get_result();

    if ($result->num_rows === 0) {
        die("<div class='alert alert-danger'>❌ Error: Project ID '$project_id' not found in 'projects' table.</div></div></body></html>");
    }

    $file_tmp = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file_tmp);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $delete_stmt = $conn->prepare("DELETE FROM apartments WHERE project_id = ?");
        $delete_stmt->bind_param("s", $project_id);
        $delete_stmt->execute();

        $inserted = 0;
        $insert_stmt = $conn->prepare("INSERT INTO apartments (project_id, unit_number, floor, bedrooms, bathrooms, area_sqm, price, payment_type, cash_discount, installment_plan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (count($row) < 9) continue;
            list($unit_number, $floor, $bedrooms, $bathrooms, $area, $price, $payment_type, $cash_discount, $installment_plan) = $row;
            $price = floatval(str_replace([',', 'AED', ' '], '', $price));
            $insert_stmt->bind_param("ssiiidddss", $project_id, $unit_number, $floor, $bedrooms, $bathrooms, $area, $price, $payment_type, $cash_discount, $installment_plan);
            if ($insert_stmt->execute()) {
                $inserted++;
            }
        }

        // ✅ Insert into upload_logs
        $log_stmt = $conn->prepare("INSERT INTO upload_logs (user_id, user_name, project_id, file_name, total_units) VALUES (?, ?, ?, ?, ?)");
        $log_stmt->bind_param("isssi", $user_id, $user_name, $project_id, $file_name, $inserted);
        $log_stmt->execute();

        echo "<div class='alert alert-success'>$inserted apartments uploaded successfully to project ID: $project_id</div>";
        echo "<a href='upload_form.php' class='btn btn-primary mt-3'>Return to Upload</a></div>";
        echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script></body></html>";
    } catch (Exception $e) {
        die("<div class='alert alert-danger'>Error reading Excel file: " . $e->getMessage() . "</div></div></body></html>");
    }
} else {
    die("<div class='alert alert-danger'>Invalid request method.</div>");
}
