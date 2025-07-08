<?php
session_start();

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: ../admin/admin_panel.php");
    exit();
}

$error = '';
$error_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "sats_db";

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        $error = "Database connection failed";
        $error_type = "error";
    } else {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];

        // 1. Find staff by email
        $staff_sql = "SELECT * FROM staffs WHERE email = '$email'";
        $staff_result = $conn->query($staff_sql);

        if ($staff_result && $staff_result->num_rows > 0) {
            $staff = $staff_result->fetch_assoc();
            $staff_id = $staff['staff_id'];

            // 2. Find password hash in logins table
            $login_sql = "SELECT * FROM logins WHERE user_id = $staff_id AND user_type = 'staff'";
            $login_result = $conn->query($login_sql);

            if ($login_result && $login_result->num_rows > 0) {
                $login = $login_result->fetch_assoc();
                if (password_verify($password, $login['password_hash'])) {
                    if ($staff['role'] === 'admin') {
                        $_SESSION['admin_id'] = $staff['staff_id'];
                        $_SESSION['admin_name'] = $staff['staff_name'];
                        header("Location: ../admin/admin_panel.php");
                        exit();
                    } else {
                        $error = "You do not have admin access.";
                        $error_type = "error";
                    }
                } else {
                    $error = "Invalid password";
                    $error_type = "error";
                }
            } else {
                $error = "No login credentials found for this staff.";
                $error_type = "error";
            }
        } else {
            $error = "Invalid email";
            $error_type = "error";
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <title>Admin Login</title>
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
        <h2>Admin Login</h2>
        <?php if (!empty($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <h3><a href="#" style="color: blue">Forgot Password?</a></h3>
            <button type="submit">Login</button>
        </form>
        <p style="color: black;">
            <b>Back to </b>
            <a href="../index.php" style="color: #0056b3;">
                <u>Home</u>
            </a>
        </p>
    </div>
</body>
</html> 