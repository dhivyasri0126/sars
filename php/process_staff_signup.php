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

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $department = $conn->real_escape_string($_POST['department']);
    $designation = $conn->real_escape_string($_POST['designation']);

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($department) || empty($designation)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if email already exists
        $check_email = "SELECT * FROM staff WHERE email = '$email'";
        $result = $conn->query($check_email);
        
        if ($result->num_rows > 0) {
            $error = "Email already registered";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new staff member
            $sql = "INSERT INTO staff (name, email, password, department, designation) 
                    VALUES ('$name', '$email', '$hashed_password', '$department', '$designation')";
            
            if ($conn->query($sql) === TRUE) {
                // Get the staff ID of the newly created account
                $staff_id = $conn->insert_id;
                
                // Set session variables for the new staff member
                $_SESSION['staff_id'] = $staff_id;
                $_SESSION['staff_name'] = $name;
                $_SESSION['staff_email'] = $email;
                $_SESSION['staff_department'] = $department;
                $_SESSION['staff_designation'] = $designation;
                
                // Redirect to staff dashboard
                header("Location: ../dashboard/staff/index.php");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }

    // If there's an error, redirect back to signup page with error message
    if (!empty($error)) {
        header("Location: ../auth/staff_signup.html?error=" . urlencode($error));
        exit();
    }
}

$conn->close();
?> 