<?php
// export_pdf.php
require_once 'auth.php';
require_capability('export_data');

require 'db_connection.php';
require 'vendor/autoload.php';
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


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

$html = '<h2>Apartment Listings</h2><table border="1" cellspacing="0" cellpadding="4">
<tr>
<th>Developer</th><th>Project</th><th>Unit</th><th>Floor</th><th>Bedrooms</th><th>Bathrooms</th><th>Area (sqm)</th><th>Price</th><th>Payment</th>
</tr>';

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['developer_name']?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['project_name']?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['unit_number']?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['floor']?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['bedrooms']?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['bathrooms']?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['area_sqm']?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars(number_format($row['price']?? '', 2)) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['payment_type']?? '') . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="9">No apartments found.</td></tr>';
}
$html .= '</table>';

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("apartments_export.pdf", ["Attachment" => true]);
exit;
