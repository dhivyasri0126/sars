<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Connect to MySQL database
$host = "localhost";
$username = "root";
$password = "";
$database = "student_portal";

$conn = new mysqli($host, $username, $password, $database);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Get student details from session
$regNo = $_SESSION['reg_number'] ?? '';
$fname = $_SESSION['first_name'] ?? '';
$lname = $_SESSION['last_name'] ?? '';
// Get POST data safely (only activity details)
$activity = $conn->real_escape_string($_POST['activity']);
$date_from = $conn->real_escape_string($_POST['date-from']);
$date_to = $conn->real_escape_string($_POST['date-to']);
$college = $conn->real_escape_string($_POST['college']);
$event_type = $conn->real_escape_string($_POST['activity-type']);
$event_name = $conn->real_escape_string($_POST['event-name']);
$award = $conn->real_escape_string($_POST['award']);
// Insert into database
$sql = "INSERT INTO activities (
            first_name, last_name, register_no, activity_type, date_from, date_to, 
            college, event_type, event_name, award
        ) VALUES (
            '$fname', '$lname', '$regNo', '$activity', '$date_from', '$date_to',
            '$college', '$event_type', '$event_name', '$award'
        )";
if ($conn->query($sql) === TRUE) {
    echo "Activity submitted successfully.";
} else {
    echo "Error: " . $conn->error;
}
$conn->close();
?>
