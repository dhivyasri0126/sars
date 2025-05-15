<?php
session_start();
if (!isset($_SESSION['reg_number'])) {
    die('Not authorized');
}

if (!isset($_GET['file'])) {
    die('No file specified');
}

$file_path = $_GET['file'];

// Security check: Ensure the file is within the uploads directory
if (strpos($file_path, 'uploads/') !== 0) {
    die('Invalid file path');
}

// Security check: Ensure the file belongs to the current user
$user_upload_dir = 'uploads/' . $_SESSION['reg_number'] . '/';
if (strpos($file_path, $user_upload_dir) !== 0) {
    die('Unauthorized access');
}

// Check if file exists
if (!file_exists($file_path)) {
    die('File not found');
}

// Set headers for PDF viewing
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Output the file
readfile($file_path);
?> 