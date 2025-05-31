<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: ../../auth/staff_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $regno = $_POST['regno'];
    $department = $_POST['department'];
    $year = $_POST['academic_year'];
    $section = $_POST['section'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $mobile = $_POST['mobile'];
    $hostel_day = $_POST['hostel_day'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Get and validate address
    $address = isset($_POST['complete_address']) ? trim($_POST['complete_address']) : '';
    if (empty($address)) {
        $_SESSION['error'] = "Address is required!";
        header("Location: students.php");
        exit();
    }

    // Database connection
    $conn = new mysqli("localhost", "root", "", "student_portal");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if registration number already exists
    $check_sql = "SELECT reg_number FROM students WHERE reg_number = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $regno);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $_SESSION['error'] = "Registration number already exists!";
        header("Location: students.php");
        exit();
    }
    $check_stmt->close();

    // Insert new student
    $sql = "INSERT INTO students (name, reg_number, department, academic_year, section, dob, gender, mobile, hostel_day, address, email, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
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