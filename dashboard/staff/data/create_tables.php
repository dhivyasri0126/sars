<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "staff_signup";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Activities table created successfully<br>";
} else {
    echo "Error creating activities table: " . $conn->error . "<br>";
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
    [1, 'Project Submission', 'approved', '2024-03-15'],
    [2, 'Internship Report', 'pending', '2024-03-14'],
    [3, 'Workshop Attendance', 'rejected', '2024-03-13'],
    [4, 'Research Paper', 'pending', '2024-03-12'],
    [5, 'Conference Presentation', 'approved', '2024-03-11']
];

$stmt = $conn->prepare("INSERT INTO activities (student_id, title, status, date) VALUES (?, ?, ?, ?)");

foreach ($activities as $activity) {
    $stmt->bind_param("isss", $activity[0], $activity[1], $activity[2], $activity[3]);
    $stmt->execute();
}

echo "Sample activities data inserted successfully<br>";

$conn->close();
?> 