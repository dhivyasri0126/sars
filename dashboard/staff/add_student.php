<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: ../../auth/staff_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate required fields
    $required_fields = ['name', 'regno', 'department', 'academic_year', 'section', 'dob', 'gender', 'mobile', 'hostel_day', 'email', 'password', 'complete_address'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $_SESSION['error'] = ucfirst(str_replace('_', ' ', $field)) . " is required!";
            header("Location: students.php");
            exit();
        }
    }

    // Get and sanitize form data
    $name = trim($_POST['name']);
    $regno = trim($_POST['regno']);
    $department = trim($_POST['department']);
    $year = trim($_POST['academic_year']);
    $section = trim($_POST['section']);
    $dob = trim($_POST['dob']);
    $gender = trim($_POST['gender']);
    $mobile = trim($_POST['mobile']);
    $hostel_day = trim($_POST['hostel_day']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = trim($_POST['complete_address']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format!";
        header("Location: students.php");
        exit();
    }

    // Validate mobile number
    if (!preg_match('/^\d{10}$/', $mobile)) {
        $_SESSION['error'] = "Invalid mobile number format!";
        header("Location: students.php");
        exit();
    }

    // Database connection
    $conn = new mysqli("localhost", "root", "", "student_portal");

    if ($conn->connect_error) {
        $_SESSION['error'] = "Connection failed: " . $conn->connect_error;
        header("Location: students.php");
        exit();
    }

    // Check if registration number already exists
    $check_sql = "SELECT reg_number FROM students WHERE reg_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        $_SESSION['error'] = "Prepare failed: " . $conn->error;
        header("Location: students.php");
        exit();
    }
    
    $check_stmt->bind_param("s", $regno);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $_SESSION['error'] = "Registration number already exists!";
        header("Location: students.php");
        exit();
    }
    $check_stmt->close();

    // Check if email already exists
    $check_email_sql = "SELECT email FROM students WHERE email = ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    if (!$check_email_stmt) {
        $_SESSION['error'] = "Prepare failed: " . $conn->error;
        header("Location: students.php");
        exit();
    }
    
    $check_email_stmt->bind_param("s", $email);
    $check_email_stmt->execute();
    $check_email_stmt->store_result();

    if ($check_email_stmt->num_rows > 0) {
        $_SESSION['error'] = "Email already exists!";
        header("Location: students.php");
        exit();
    }
    $check_email_stmt->close();

    // Insert new student
    $sql = "INSERT INTO students (name, reg_number, department, academic_year, section, dob, gender, mobile, hostel_day, address, email, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = "Prepare failed: " . $conn->error;
        header("Location: students.php");
        exit();
    }

    $stmt->bind_param("ssssssssssss", $name, $regno, $department, $year, $section, $dob, $gender, $mobile, $hostel_day, $address, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Student added successfully!";
    } else {
        $_SESSION['error'] = "Error adding student: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: students.php");
    exit();
} else {
    header("Location: students.php");
    exit();
}
?> 