<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "staff_signup"; // Replace with your actual DB name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'];
$dob = $_POST['dob'];
$designation = $_POST['designation'];
$department = $_POST['department'];
$gender = $_POST['gender'];
$mobile = $_POST['mobile'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // secure

// Insert query
$sql = "INSERT INTO staff_signup (name, dob, designation, department, gender, mobile, email, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $name, $dob, $designation, $department, $gender, $mobile, $email, $password);

if ($stmt->execute()) {
    echo "Signup successful!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
