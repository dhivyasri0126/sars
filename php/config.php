<?php
// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "staff_signup";

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Function to sanitize user input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Function to verify password
function verify_password($input_password, $hashed_password) {
    return password_verify($input_password, $hashed_password);
}

// Function to hash password
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
?> 