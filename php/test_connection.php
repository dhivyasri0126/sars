<?php
require_once 'config.php';

// Test database connection
echo "<h2>Database Connection Test</h2>";
if ($conn->connect_error) {
    echo "<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>Connection successful!</p>";
}

// Check if database exists
$result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'student_activity_record'");
if ($result->num_rows > 0) {
    echo "<p style='color: green;'>Database 'student_activity_record' exists</p>";
} else {
    echo "<p style='color: red;'>Database 'student_activity_record' does not exist</p>";
}

// Check tables
$tables = ['staff', 'students', 'activities', 'student_activities', 'activity_attendance', 'od_applications', 'other_college_events'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>Table '$table' exists</p>";
    } else {
        echo "<p style='color: red;'>Table '$table' does not exist</p>";
    }
}

// Test sample data
echo "<h2>Sample Data Test</h2>";

// Check staff table
$result = $conn->query("SELECT COUNT(*) as count FROM staff");
$row = $result->fetch_assoc();
echo "<p>Staff records: " . $row['count'] . "</p>";

// Check students table
$result = $conn->query("SELECT COUNT(*) as count FROM students");
$row = $result->fetch_assoc();
echo "<p>Student records: " . $row['count'] . "</p>";

// Check activities table
$result = $conn->query("SELECT COUNT(*) as count FROM activities");
$row = $result->fetch_assoc();
echo "<p>Activity records: " . $row['count'] . "</p>";

// Display sample staff data
echo "<h3>Sample Staff Data</h3>";
$result = $conn->query("SELECT * FROM staff");
if ($result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Designation</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['id'] . "</td><td>" . $row['name'] . "</td><td>" . $row['email'] . "</td><td>" . $row['department'] . "</td><td>" . $row['designation'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No staff records found</p>";
}

// Display sample student data
echo "<h3>Sample Student Data</h3>";
$result = $conn->query("SELECT * FROM students");
if ($result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Roll Number</th><th>Year</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['id'] . "</td><td>" . $row['name'] . "</td><td>" . $row['email'] . "</td><td>" . $row['department'] . "</td><td>" . $row['roll_number'] . "</td><td>" . $row['year'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No student records found</p>";
}

$conn->close();
?> 