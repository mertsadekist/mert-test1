<?php
// process_upload.php
require_once 'auth.php';
require_capability('upload_apartments');

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';
require_once 'csrf.php';

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    $developer_id = $_POST['developer_id'] ?? '';
    $project_id = $_POST['project_id'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;
    $user_name = $_SESSION['user_name'] ?? 'unknown';
    $file_name = $_FILES['excel_file']['name'] ?? '';

    echo "<html><head><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'></head><body>";
    echo "<nav class='navbar navbar-expand-lg navbar-dark bg-dark'>
            <div class='container-fluid'>
                <a class='navbar-brand' href='#'>Real Estate Admin</a>
                <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav'>
                    <span class='navbar-toggler-icon'></span>
                </button>
                <div class='collapse navbar-collapse' id='navbarNav'>
                    <ul class='navbar-nav'>
                        <li class='nav-item'><a class='nav-link' href='dashboard.php'>Dashboard</a></li>
                        <li class='nav-item'><a class='nav-link' href='upload_form.php'>Upload Excel</a></li>
                        <li class='nav-item'><a class='nav-link' href='manage_developers_projects.php'>Manage Projects</a></li>
                        <li class='nav-item'><a class='nav-link' href='display_apartments.php'>View By Project</a></li>
                        <li class='nav-item'><a class='nav-link' href='all_apartments.php'>Filter All Apartments</a></li>
                        <li class='nav-item'><a class='nav-link' href='register_user.php'>Register User</a></li>
                        <li class='nav-item'><a class='nav-link' href='logout.php'>Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>";

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
