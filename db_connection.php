<?php
// db_connection.php
$host = 'localhost';
$db = 'u891594679_stoks';
$user = 'u891594679_stoks';
$pass = '^1QQHgpeQ7o';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
