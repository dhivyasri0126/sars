<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to database
$conn = new mysqli("localhost", "root", "", "student_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare SQL statement
    $sql = "SELECT reg_number, name, department, academic_year, section, dob, gender, mobile, hostel_day, address, email, password FROM students WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a student was found
    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();

        // Compare hashed password
        if (password_verify($password, $student['password'])) {
            $_SESSION['reg_number'] = $student['reg_number'];
            $_SESSION['name'] = $student['name'];
            $_SESSION['department'] = $student['department'];
            $_SESSION['academic_year'] = $student['academic_year'];
            $_SESSION['section'] = $student['section'];
            $_SESSION['dob'] = $student['dob'];
            $_SESSION['gender'] = $student['gender'];
            $_SESSION['mobile'] = $student['mobile'];
            $_SESSION['hostel_day'] = $student['hostel_day'];
            $_SESSION['address'] = $student['address'];
            $_SESSION['email'] = $student['email'];
            header("Location: ../dashboard/student/index.php");
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Invalid email or user not found.";
    }
    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <title>Student Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('../assets/images/main.jpg');
            background-size: cover;
            backdrop-filter: blur(4px);
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px 0;
        }
        
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 10px 20px 0px rgba(50, 50, 50, 0.52);
            max-width: 90%;
            text-align: center;
        }

        .login-logo {
            width: 105px;
            height: 105px;
            margin-bottom: 10px;
            border-radius: 50%;
        }
        
        h2 {
            color: black;
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin: 20px 0 20px;
            font-weight: bold;
            font-size: 120%;
            text-align: left;
        }
        
        input[type="email"], 
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 20px;
            font-size: 16px;
        }
        
        button {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #0056b3;
        }
        
        .error-message {
            color: red;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .success-message {
            color: green;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        @media only screen and (max-width: 600px) {
            .login-container {
                max-width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="../assets/images/logo.png" alt="logo" class="login-logo">
        <h2>Student Login</h2>
        
        <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
        <div class="success-message">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            
            <h3><a href="fydyrd.html" style="color: blue">Forgot Password?</a></h3>

            <button type="submit">Login</button>
        </form>
        
        <p style="color: black;">
            <b>Don't have an account?</b> 
            <a href="student_signup.php" style="color: #0056b3;">
                <u>Register Here</u>
            </a>
        </p>
    </div>
</body>
</html>

