<?php
session_start();

// Database connection parameters
$host = "localhost";
$user = "root";
$pass = "";
$db = "staff_signup";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create staff table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS staff (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(100) NOT NULL,
    designation VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($createTableSQL)) {
    die("Error creating table: " . $conn->error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // Query to check staff credentials
    $sql = "SELECT * FROM staff WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $staff = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $staff['password'])) {
            // Set session variables
            $_SESSION['staff_id'] = $staff['id'];
            $_SESSION['staff_name'] = $staff['name'];
            $_SESSION['staff_email'] = $staff['email'];
            $_SESSION['staff_department'] = $staff['department'];
            $_SESSION['staff_designation'] = $staff['designation'];
            
            // Redirect to staff dashboard
            header("Location: ../dashboard/staff/activities.php");
            exit;
        } else {
            // Invalid password
            header("Location: ../auth/staff_login.php?error=1");
            exit;
        }
    } else {
        // Staff not found
        header("Location: ../auth/staff_login.php?error=1");
        exit;
    }
}

$conn->close();
?> 