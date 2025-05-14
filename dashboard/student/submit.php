<?php
$host = "localhost";
$user = "root";         // use your DB username
$password = "";         // use your DB password
$dbname = "student_portal"; // your DB name

// Connect to MySQL
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// File upload handling
if (isset($_FILES['file'])) {
    $fileName = $_FILES['file']['name'];
    $fileTmp  = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileType = $_FILES['file']['type'];
    $uploadDir = "uploads/";

    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $destination = $uploadDir . basename($fileName);

    if (move_uploaded_file($fileTmp, $destination)) {
        // Insert file data into database
        $stmt = $conn->prepare("INSERT INTO uploads (file_name, file_type, file_size, upload_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ssi", $fileName, $fileType, $fileSize);

        if ($stmt->execute()) {
            echo "File uploaded and database entry created successfully.";
        } else {
            echo "Database error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Failed to move uploaded file.";
    }
} else {
    echo "No file uploaded.";
}

$conn->close();
?>
