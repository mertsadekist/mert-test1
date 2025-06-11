<?php
// create_admin.php — Run this once to insert the first admin user

require_once 'db_connection.php';

// Change these as needed
$name = 'Admin';
$email = 'admin@example.com';
$password_plain = 'admin123';
$role = 'admin';
$id = uniqid();

// Hash the password
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

// Check if user already exists
$check = $conn->query("SELECT id FROM users WHERE email = '$email'");
if ($check->num_rows > 0) {
    echo "<p>User with email $email already exists.</p>";
} else {
    $sql = "INSERT INTO users (id, name, email, password_hash, role) VALUES ('$id', '$name', '$email', '$password_hashed', '$role')";
    if ($conn->query($sql)) {
        echo "<p>Admin user created successfully.<br>Email: $email<br>Password: $password_plain</p>";
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}

$conn->close();
?>
