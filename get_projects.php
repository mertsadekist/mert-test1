<?php
// get_projects.php
require_once 'db_connection.php';

if (!isset($_GET['developer_id'])) {
    echo json_encode([]);
    exit;
}

$developer_id = $conn->real_escape_string($_GET['developer_id']);
$sql = "SELECT id, name FROM projects WHERE developer_id = '$developer_id' ORDER BY name";
$result = $conn->query($sql);

$projects = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($projects);
