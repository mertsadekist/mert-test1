<?php
// db_connection.php
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_DATABASE') ?: 'u891594679_stoks';
$user = getenv('DB_USER') ?: 'u891594679_stoks';
$pass = getenv('DB_PASS') ?: '^1QQHgpeQ7o';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
