<?php
// get_projects.php
require_once 'db_connection.php';

if (!isset($_GET['developer_id'])) {
    echo json_encode([]);
    exit;
}

$developer_id = $_GET['developer_id'];
$stmt = $conn->prepare("SELECT id, name FROM projects WHERE developer_id = ? ORDER BY name");
$stmt->bind_param('s', $developer_id);
$stmt->execute();
$result = $stmt->get_result();

$projects = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($projects);
