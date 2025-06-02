<?php
// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db_students = "staff_signup";

// Create connection
$conn = new mysqli($host, $user, $pass, $db_students);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}
?> 