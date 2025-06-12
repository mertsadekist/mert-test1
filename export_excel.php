<?php
// export_excel.php
require_once 'auth.php';
require_once 'roles.php';
require_role([ROLE_EDITOR, ROLE_ADMIN]);

require 'db_connection.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filters = [];
$where = [];

if (!empty($_GET['developer_id'])) {
    $filters['developer_id'] = $_GET['developer_id'];
    $where[] = "projects.developer_id = '" . $conn->real_escape_string($_GET['developer_id']) . "'";
}
if (!empty($_GET['project_id'])) {
    $filters['project_id'] = $_GET['project_id'];
    $where[] = "apartments.project_id = '" . $conn->real_escape_string($_GET['project_id']) . "'";
}
if (!empty($_GET['location'])) {
    $filters['location'] = $_GET['location'];
    $where[] = "projects.location LIKE '%" . $conn->real_escape_string($_GET['location']) . "%'";
}
if (!empty($_GET['bedrooms'])) {
    $filters['bedrooms'] = $_GET['bedrooms'];
    $where[] = "apartments.bedrooms = '" . intval($_GET['bedrooms']) . "'";
}
if (!empty($_GET['area_min'])) {
    $filters['area_min'] = $_GET['area_min'];
    $where[] = "apartments.area_sqm >= '" . floatval($_GET['area_min']) . "'";
}
if (!empty($_GET['area_max'])) {
    $filters['area_max'] = $_GET['area_max'];
    $where[] = "apartments.area_sqm <= '" . floatval($_GET['area_max']) . "'";
}
if (!empty($_GET['price_min'])) {
    $filters['price_min'] = $_GET['price_min'];
    $where[] = "apartments.price >= '" . floatval($_GET['price_min']) . "'";
}
if (!empty($_GET['price_max'])) {
    $filters['price_max'] = $_GET['price_max'];
    $where[] = "apartments.price <= '" . floatval($_GET['price_max']) . "'";
}

$where_clause = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT apartments.*, projects.name AS project_name, developers.name AS developer_name FROM apartments
        JOIN projects ON apartments.project_id = projects.id
        JOIN developers ON projects.developer_id = developers.id
        $where_clause
        ORDER BY apartments.id DESC";

$result = $conn->query($sql);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'Developer');
$sheet->setCellValue('B1', 'Project');
$sheet->setCellValue('C1', 'Unit');
$sheet->setCellValue('D1', 'Floor');
$sheet->setCellValue('E1', 'Bedrooms');
$sheet->setCellValue('F1', 'Bathrooms');
$sheet->setCellValue('G1', 'Area (sqm)');
$sheet->setCellValue('H1', 'Price');
$sheet->setCellValue('I1', 'Payment Type');

$rowIndex = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue("A$rowIndex", $row['developer_name']);
    $sheet->setCellValue("B$rowIndex", $row['project_name']);
    $sheet->setCellValue("C$rowIndex", $row['unit_number']);
    $sheet->setCellValue("D$rowIndex", $row['floor']);
    $sheet->setCellValue("E$rowIndex", $row['bedrooms']);
    $sheet->setCellValue("F$rowIndex", $row['bathrooms']);
    $sheet->setCellValue("G$rowIndex", $row['area_sqm']);
    $sheet->setCellValue("H$rowIndex", $row['price']);
    $sheet->setCellValue("I$rowIndex", $row['payment_type']);
    $rowIndex++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="apartments_export.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
