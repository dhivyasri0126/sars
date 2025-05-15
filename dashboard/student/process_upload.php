<?php
session_start();
if (!isset($_SESSION['reg_number'])) {
    die('Not authorized');
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die('No file uploaded or upload error occurred');
}

// Check if activity_id is provided
if (!isset($_POST['activity_id'])) {
    die('Activity ID is required');
}

$activity_id = (int)$_POST['activity_id'];
$file = $_FILES['file'];

// Validate file type
$allowed_types = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/gif'
];
$file_type = mime_content_type($file['tmp_name']);
if (!in_array($file_type, $allowed_types)) {
    die('Only PDF and image files (JPG, PNG, GIF) are allowed');
}

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/' . $_SESSION['reg_number'] . '/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate unique filename
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$new_filename = uniqid() . '.' . $file_extension;
$file_path = $upload_dir . $new_filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $file_path)) {
    // Update database with file path
    $conn = new mysqli("localhost", "root", "", "student_portal");
    if ($conn->connect_error) {
        unlink($file_path); // Delete the uploaded file
        die('Database connection failed');
    }

    // First get the student's ID
    $sql = "SELECT id FROM students WHERE reg_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['reg_number']);
    $stmt->execute();
    $student_result = $stmt->get_result();
    $student = $student_result->fetch_assoc();
    $student_id = $student['id'];

    // Verify that the activity belongs to the student
    $sql = "SELECT id FROM activities WHERE id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $activity_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        unlink($file_path); // Delete the uploaded file
        die('Activity not found or unauthorized');
    }

    // Update the file path in the database
    $sql = "UPDATE activities SET file_path = ? WHERE id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $file_path, $activity_id, $student_id);
    
    if ($stmt->execute()) {
        // Set proper permissions for the uploaded file
        chmod($file_path, 0644); // Readable by all, writable only by owner
        echo 'success';
    } else {
        unlink($file_path); // Delete the uploaded file
        die('Failed to update database');
    }

    $stmt->close();
    $conn->close();
} else {
    die('Failed to move uploaded file');
}
?> 