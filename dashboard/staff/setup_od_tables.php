<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Create od_requests table
$sql = "CREATE TABLE IF NOT EXISTS od_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    event_name VARCHAR(255) NOT NULL,
    event_type ENUM('Technical', 'Non-Technical', 'Both') NOT NULL,
    event_date DATE NOT NULL,
    tutor_approval ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    advisor_approval ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    hod_approval ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    certificate_path VARCHAR(255),
    certificate_upload_date DATETIME,
    status ENUM('Pending', 'Tutor Approved', 'Advisor Approved', 'HOD Approved', 'Awaiting Certificate', 'Confirmed', 'Expired') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table od_requests created successfully<br>";
} else {
    echo "Error creating table od_requests: " . $conn->error . "<br>";
}

// Create od_comments table
$sql = "CREATE TABLE IF NOT EXISTS od_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    od_id INT NOT NULL,
    staff_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (od_id) REFERENCES od_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table od_comments created successfully<br>";
} else {
    echo "Error creating table od_comments: " . $conn->error . "<br>";
}

$conn->close();
?> 