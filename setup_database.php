<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "staff_signup";

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $db";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Create student_portal database
$student_db = "student_portal";
$sql = "CREATE DATABASE IF NOT EXISTS $student_db";
if ($conn->query($sql) === TRUE) {
    echo "Student portal database created successfully<br>";
} else {
    echo "Error creating student portal database: " . $conn->error . "<br>";
}

// Select the staff database
$conn->select_db($db);

// Create staff table
$sql = "CREATE TABLE IF NOT EXISTS staff (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(100),
    designation VARCHAR(100),
    role ENUM('tutor', 'advisor', 'hod', 'none') DEFAULT 'none',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Staff table created successfully<br>";
} else {
    echo "Error creating staff table: " . $conn->error . "<br>";
}

// Select the student portal database
$conn->select_db($student_db);

// Create students table
$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    grade VARCHAR(2),
    activity_count INT DEFAULT 0,
    department VARCHAR(100),
    year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Students table created successfully<br>";
} else {
    echo "Error creating students table: " . $conn->error . "<br>";
}

// Create activities table
$sql = "CREATE TABLE IF NOT EXISTS activities (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    student_id INT(11),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'tutor_approved', 'advisor_approved', 'hod_approved', 'approved', 'rejected') DEFAULT 'pending',
    file_path VARCHAR(255),
    date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Activities table created successfully<br>";
} else {
    echo "Error creating activities table: " . $conn->error . "<br>";
}

// Insert sample staff data
$password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO staff (name, email, password, department, designation) 
        VALUES ('Admin User', 'admin@example.com', '$password', 'Administration', 'System Administrator')";

if ($conn->query($sql) === TRUE) {
    echo "Sample staff data inserted successfully<br>";
} else {
    echo "Error inserting staff data: " . $conn->error . "<br>";
}

// Insert sample students data
$students = [
    ['John Doe', 'A', 5, 'Computer Science', 2023],
    ['Jane Smith', 'B+', 3, 'Electrical Engineering', 2023],
    ['Mike Johnson', 'A-', 4, 'Mechanical Engineering', 2023],
    ['Sarah Williams', 'B', 2, 'Computer Science', 2023],
    ['David Brown', 'A+', 6, 'Electrical Engineering', 2023]
];

$stmt = $conn->prepare("INSERT INTO students (name, grade, activity_count, department, year) VALUES (?, ?, ?, ?, ?)");

foreach ($students as $student) {
    $stmt->bind_param("ssisi", $student[0], $student[1], $student[2], $student[3], $student[4]);
    $stmt->execute();
}

echo "Sample students data inserted successfully<br>";

// Insert sample activities data
$activities = [
    [1, 'Project Submission', '2024-03-15'],
    [2, 'Internship Report', '2024-03-14'],
    [3, 'Workshop Attendance', '2024-03-13'],
    [4, 'Research Paper', '2024-03-12'],
    [5, 'Conference Presentation', '2024-03-11']
];

$stmt = $conn->prepare("INSERT INTO activities (student_id, title, date) VALUES (?, ?, ?)");

foreach ($activities as $activity) {
    $stmt->bind_param("iss", $activity[0], $activity[1], $activity[2]);
    $stmt->execute();
}

echo "Sample activities data inserted successfully<br>";

$conn->close();
?> 