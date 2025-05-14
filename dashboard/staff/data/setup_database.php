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

// Select the database
$conn->select_db($db);

// Create staff table
$sql = "CREATE TABLE IF NOT EXISTS staff (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(100),
    designation VARCHAR(100),
    phone VARCHAR(20),
    gender ENUM('Male', 'Female', 'Other'),
    date_of_birth DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Staff table created successfully<br>";
} else {
    echo "Error creating staff table: " . $conn->error . "<br>";
}

// Create students table
$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(20) NOT NULL UNIQUE,
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

// Insert sample staff data
$password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO staff (name, email, password, department, designation, phone, gender, date_of_birth) 
        VALUES ('Admin User', 'admin@example.com', '$password', 'Administration', 'System Administrator', '1234567890', 'Male', '1990-01-01')";

if ($conn->query($sql) === TRUE) {
    echo "Sample staff data inserted successfully<br>";
} else {
    echo "Error inserting staff data: " . $conn->error . "<br>";
}

// Insert sample students data
$students = [
    ['CS2020001', 'John Doe', 'A', 5, 'Computer Science', 2020],
    ['CS2020002', 'Jane Smith', 'B+', 3, 'Computer Science', 2020],
    ['EE2021001', 'Mike Johnson', 'A-', 4, 'Electrical Engineering', 2021],
    ['ME2021001', 'Sarah Williams', 'B', 2, 'Mechanical Engineering', 2021],
    ['CS2022001', 'David Brown', 'A+', 6, 'Computer Science', 2022],
    ['EE2022001', 'Emily Davis', 'A', 4, 'Electrical Engineering', 2022],
    ['ME2023001', 'Robert Wilson', 'B+', 3, 'Mechanical Engineering', 2023],
    ['CS2023001', 'Lisa Anderson', 'A-', 5, 'Computer Science', 2023],
    ['EE2024001', 'Michael Taylor', 'B', 2, 'Electrical Engineering', 2024],
    ['ME2024001', 'Jennifer Martinez', 'A', 4, 'Mechanical Engineering', 2024]
];

$stmt = $conn->prepare("INSERT INTO students (roll_number, name, grade, activity_count, department, year) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($students as $student) {
    $stmt->bind_param("sssisi", $student[0], $student[1], $student[2], $student[3], $student[4], $student[5]);
    $stmt->execute();
}

echo "Sample students data inserted successfully<br>";

// Insert sample activities data
$activities = [
    [1, 'Project Submission', 'approved', '2024-03-15'],
    [2, 'Internship Report', 'pending', '2024-03-14'],
    [3, 'Workshop Attendance', 'rejected', '2024-03-13'],
    [4, 'Research Paper', 'pending', '2024-03-12'],
    [5, 'Conference Presentation', 'approved', '2024-03-11'],
    [6, 'Hackathon Participation', 'approved', '2024-03-10'],
    [7, 'Technical Workshop', 'pending', '2024-03-09'],
    [8, 'Project Demo', 'approved', '2024-03-08'],
    [9, 'Internship Report', 'pending', '2024-03-07'],
    [10, 'Research Presentation', 'approved', '2024-03-06']
];

$stmt = $conn->prepare("INSERT INTO activities (student_id, title, status, date) VALUES (?, ?, ?, ?)");

foreach ($activities as $activity) {
    $stmt->bind_param("isss", $activity[0], $activity[1], $activity[2], $activity[3]);
    $stmt->execute();
}

echo "Sample activities data inserted successfully<br>";

$conn->close();
?> 