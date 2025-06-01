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
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Get and validate address
    $address = isset($_POST['complete_address']) ? trim($_POST['complete_address']) : '';
    if (empty($address)) {
        echo "<script>alert('Address is required!');</script>";
        exit();
    }

    // Replace these with your actual DB credentials
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
        echo "<script>alert('Registration number already exists!');</script>";
        exit();
    }
    $check_stmt->close();

    $sql = "INSERT INTO students (name, reg_number, department, academic_year, section, dob, gender, mobile, hostel_day, address, email, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssss", $name, $regno, $department, $year, $section, $dob, $gender, $mobile, $hostel_day, $address, $email, $password);

    if ($stmt->execute()) {
        // Set session variables for the new student
        session_start();
        $_SESSION['reg_number'] = $regno;
        $_SESSION['name'] = $name;
        $_SESSION['department'] = $department;
        $_SESSION['academic_year'] = $year;
        $_SESSION['section'] = $section;
        $_SESSION['dob'] = $dob;
        $_SESSION['gender'] = $gender;
        $_SESSION['mobile'] = $mobile;
        $_SESSION['hostel_day'] = $hostel_day;
        $_SESSION['address'] = $address;
        $_SESSION['email'] = $email;
        
        // Check if we're in an iframe
        if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'students.php') !== false) {
            // Send message to parent window
            echo "<script>
                window.parent.postMessage('studentAdded', '*');
            </script>";
            exit();
        }
        
        // Redirect to dashboard if not in iframe
        header('Location: ../dashboard/student/index.php');
        exit();
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
            background: transparent;
            backdrop-filter: none;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px 0;
        }
        
        .signup-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            max-width: 600px;
            width: 90%;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }

        .signup-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.4) 0%, rgba(255, 255, 255, 0.1) 100%);
            z-index: -1;
        }
        
        .signup-logo {
            width: 105px;
            height: 105px;
            margin-bottom: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            padding: 5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: #1a1a1a;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            padding: 10px;
            color: #1a1a1a;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            font-size: 16px;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.8);
            color: #1a1a1a;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="date"]:focus,
        select:focus {
            outline: none;
            border: 1px solid rgba(0, 123, 255, 0.5);
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.2);
            background: rgba(255, 255, 255, 0.95);
        }
        
        button {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #0056b3 0%, #003d82 100%);
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
                    <!-- Address Fields -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Address Information</h3>
                        
                        <!-- Street Address -->
                        <div>
                            <label for="street" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                            <input type="text" name="street" id="street" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                placeholder="Enter your address">
                        </div>

                        <!-- City and State -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">City</label>
                                <input type="text" name="city" id="city" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="Enter your city">
                            </div>
                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">State</label>
                                <input type="text" name="state" id="state" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="Enter your state" value="Tamil Nadu">
                            </div>
                        </div>

                        <!-- Pincode and Country -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="pincode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pincode</label>
                                <input type="text" name="pincode" id="pincode" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="Enter your pincode">
                            </div>
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                                <input type="text" name="country" id="country" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="Enter your country" value="India">
                            </div>
                        </div>

                        <!-- Hidden field for complete address -->
                        <input type="hidden" name="address" id="complete_address">
                    </div>
                    <button type="submit">Submit</button><br><br>
                    <a href="student_login.php" style="color: black;">Already registered? <u>Login here</u></a>
                </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("form");
            form.addEventListener("submit", function (event) {
                event.preventDefault();
                
                // Check if passwords match
                const password = document.getElementById("password").value;
                const confirmPassword = document.getElementById("confirm-password").value;
                if (password !== confirmPassword) {
                    alert("Passwords do not match!");
                    return;
                }

                // Combine address fields
            const street = document.getElementById('street').value;
            const city = document.getElementById('city').value;
            const state = document.getElementById('state').value;
            const pincode = document.getElementById('pincode').value;
            const country = document.getElementById('country').value;
            
                // Create a complete address string
            const completeAddress = `${street}, ${city}, ${state}, ${country} - ${pincode}`;
            document.getElementById('complete_address').value = completeAddress;
                
                // Submit the form
                this.submit();
            });
        });
    </script>
</body>
</html>
