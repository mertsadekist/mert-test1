<?php
// display_apartments.php with advanced filter
require_once 'db_connection.php';

$selected_developer = $_POST['developer_id'] ?? '';
$selected_project = $_POST['project_id'] ?? '';
$selected_location = $_POST['location'] ?? '';

$developers = $conn->query("SELECT id, name FROM developers ORDER BY name");
$projects = $selected_developer ? $conn->query("SELECT id, name FROM projects WHERE developer_id = '$selected_developer' ORDER BY name") : [];
$locations = $conn->query("SELECT DISTINCT location FROM projects WHERE location IS NOT NULL AND location != '' ORDER BY location");
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conditions = [];
    if (!empty($selected_project)) {
        $conditions[] = "project_id = '$selected_project'";
    } elseif (!empty($selected_location)) {
        $location_safe = $conn->real_escape_string($selected_location);
        $project_ids_query = $conn->query("SELECT id FROM projects WHERE location = '$location_safe'");
        $ids = [];
        while ($row = $project_ids_query->fetch_assoc()) {
            $ids[] = "'{$row['id']}'";
        }
        if ($ids) {
            $conditions[] = "project_id IN (" . implode(",", $ids) . ")";
        }
    }
