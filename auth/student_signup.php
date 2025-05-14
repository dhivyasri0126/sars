<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $regno = $_POST['regno']; 
    $department = $_POST['Department'];
    $year = $_POST['academic-year'];
    $section = $_POST['section'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $mobile = $_POST['mobile'];
    $hostel_day = $_POST['hostel_day'];
    $address = $_POST['Address'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);


    // Replace these with your actual DB credentials
    $conn = new mysqli("localhost", "root", "", "student_portal");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO students (name, reg_number, department, academic_year, section, dob, gender, mobile, hostel_day, address, email, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssss", $name, $regno, $department, $year, $section, $dob, $gender, $mobile, $hostel_day, $address, $email, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Sign-up successful!'); window.location.href='student_signup.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student SignUp</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    
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
            padding:10px;
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
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        
        }
        
        .col {
            flex: 1;
            padding: 0 10px;
            min-width: 200px;
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
       
                <form id="form" method="POST" action="">
                    <h2>Student SignUp</h2>
                    <div class="row">
                        <div class="col">
                            <div class="form-control">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" placeholder="Enter your Name" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-control">
                                <label for="regno">Register Number</label>
                                <input type="text" id="regno" name="regno" placeholder="Enter your Register Number" required>
                            </div>
                        </div> 
                    </div>
                    <div class="row">
                        <div class="col">

                            <div class="form-control">
                                <label for="Department">Department</label>
                                <select id="Department" name="Department" required>
                                    <option value="cse">CSE</option>
                                    <option value="Bme">BME</option>
                                    <option value="ai&ds">AI&DS</option>
                                    <option value="it">IT</option>
                                    <option value="ece">ECE</option>
                                    <option value="eee">EEE</option>
                                    <option value="civil">Civil</option>
                                    <option value="mech">Mechanical</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">

                            <div class="form-control">
                                <label for="academic-year">Academic Year</label>
                                <input type="text" id="academic-year" name="academic-year" placeholder="Enter Your Academic Year" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">

                            <div class="form-control">
                                <label for="section">Section</label>
                                <input type="text" id="section" name="section" placeholder="Enter your Section" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-control">
                                <label for="dob">Date Of Birth</label>
                                <input type="date" id="dob" name="dob" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-control">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender" required>
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-control">
                                <label for="mobile">Mobile Number</label>
                                <input type="text" id="mobile" name="mobile" placeholder="Enter your Mobile Number" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-control">
                                <label for="hostel_day">Hosteller/DayScholar</label>
                                <select id="hostel_day" name="hostel_day">
                                    <option value="hosteller">Hosteller</option>
                                    <option value="dayscholar">DayScholar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-control">
                                <label for="Address">Address For Communication</label>
                                <textarea placeholder="Enter your Address" name="Address" id="Address"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-control">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="Enter your email" required> 
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-control">
                                <label for="password">Create Password</label>
                                <input type="password" id="password" name="password" placeholder="Enter password" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">

                            <div class="form-control">
                                <label for="confirm-password">Confirm Password</label>
                                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm Password" required>
                            </div>
                        </div>
                        <div class="col">
                        </div>
                    </div>
                    <button type="submit">Submit</button>
                    <a href="student_login.php" style="color: white;">Already registered? <u>Login here</u></a>
                </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("form");
            form.addEventListener("submit", function (event) {
                const password = document.getElementById("password").value;
                const confirmPassword = document.getElementById("confirm-password").value;
                if (password !== confirmPassword) {
                    event.preventDefault();
                    alert("Passwords do not match!");
                }
            });
        });
    </script>
</body>
</html>
