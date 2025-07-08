<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $designation = $conn->real_escape_string($_POST['designation']);
    $role = $conn->real_escape_string($_POST['role']);
    $qualification = $conn->real_escape_string($_POST['qualification']);
    $department_id = (int)$_POST['department'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check_sql = "SELECT * FROM staffs WHERE email = '$email'";
    $result = $conn->query($check_sql);
    if ($result->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        // Insert into staffs table
        $sql = "INSERT INTO staffs (staff_name, email, designation, role, qualification, department_id) 
                VALUES ('$name', '$email', '$designation', '$role', '$qualification', $department_id)";
        
        if ($conn->query($sql)) {
            $staff_id = $conn->insert_id;
            
            // Insert into logins table
            $login_sql = "INSERT INTO logins (user_id, user_type, password_hash) 
                         VALUES ($staff_id, 'staff', '$password')";
            
            if ($conn->query($login_sql)) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: staff_login.php");
                exit();
            } else {
                $error = "Error creating login credentials: " . $conn->error;
            }
        } else {
            $error = "Error registering staff: " . $conn->error;
        }
    }
}

// Get departments for dropdown
$departments = [];
$dept_result = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
while ($row = $dept_result->fetch_assoc()) {
    $departments[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <title>Staff Signup</title>
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
        
        .signup-container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 10px 20px 0px rgba(50, 50, 50, 0.52);
            max-width: 600px;
            width: 90%;
            text-align: center;
        }
        
        .signup-logo {
            width: 105px;
            height: 105px;
            margin-bottom: 10px;
            border-radius: 50%;
        }
        
        h2 {
            color: black;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 20px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
/*        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .col {
            flex: 1;
            padding: 0 10px;
            min-width: 200px;
        }
*/        
        button {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
        }
        
        button:hover {
            background-color: #0056b3;
        }
        
        .error-message {
            color: red;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        @media only screen and (max-width: 600px) {
            .signup-container {
                max-width: 95%;
            }
            
            .col {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <img src="../assets/images/logo.png" alt="logo" class="signup-logo">
        <h2>Staff Signup</h2>
        
        <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="signupForm">
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="dob">Date of Birth:</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="designation">Designation:</label>
                        <input type="text" id="designation" name="designation" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="qualification">Qualification:</label>
                        <input type="text" id="qualification" name="qualification" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="department">Department:</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['department_id']; ?>">
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="gender">Gender:</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="role">Role:</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="tutor">Tutor</option>
                            <option value="advisor">Advisor</option>
                            <option value="hod">HOD</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="text" id="phone" name="phone" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
            </div>
            
            <button type="submit">Sign Up</button>
        </form>
        
        <p style="margin-top: 20px;">
            Already have an account? <a href="staff_login.php" style="color: #0056b3;"><u>Login Here</u></a>
        </p>
    </div>

    <script>
        // Password confirmation validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html> 