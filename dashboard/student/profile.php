<?php
session_start();
if (!isset($_SESSION['reg_number'])) {
    header('Location: ../../auth/student_login.php');
    exit();
}
// DB connection
$conn = new mysqli("localhost", "root", "", "student_portal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$reg_number = $_SESSION['reg_number'];
// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $department = $conn->real_escape_string($_POST['department']);
    $academic_year = $conn->real_escape_string($_POST['academic_year']);
    $section = $conn->real_escape_string($_POST['section']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $mobile = $conn->real_escape_string($_POST['mobile']);
    $hostel_day = $conn->real_escape_string($_POST['hostel_day']);
    $address = $conn->real_escape_string($_POST['address']);
    $email = $conn->real_escape_string($_POST['email']);
    $sql = "UPDATE students SET name=?, department=?, academic_year=?, section=?, dob=?, gender=?, mobile=?, hostel_day=?, address=?, email=? WHERE reg_number=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssss", $name, $department, $academic_year, $section, $dob, $gender, $mobile, $hostel_day, $address, $email, $reg_number);
    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['name'] = $name;
        $_SESSION['department'] = $department;
        $_SESSION['academic_year'] = $academic_year;
        $_SESSION['section'] = $section;
        $_SESSION['dob'] = $dob;
        $_SESSION['gender'] = $gender;
        $_SESSION['mobile'] = $mobile;
        $_SESSION['hostel_day'] = $hostel_day;
        $_SESSION['address'] = $address;
        $_SESSION['email'] = $email;
        $success = true;
    } else {
        $error = $stmt->error;
    }
}
// Fetch latest student info
$sql = "SELECT * FROM students WHERE reg_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reg_number);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
<div class="flex h-screen">
<div class="sidebar w-64 bg-white dark:bg-gray-800 shadow-lg flex flex-col">
                <div class="p-4 flex flex-col items-center">
                    <img src="logo.png" alt="Logo" class="w-16 h-16 rounded-full mb-2">
                    <h2 class="text-center text-xl font-bold text-gray-800 dark:text-white">Student Portal</h2>
                </div>
                <nav class="flex-1 mt-6">
                    <a href="index.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                        <i class="fas fa-home w-6"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                    <a href="activity.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                        <i class="fas fa-edit w-6"></i>
                        <span class="ml-3">Activity Participation</span>
                    </a>
                    <a href="upload.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                        <i class="fas fa-upload w-6"></i>
                        <span class="ml-3">Upload</span>
                    </a>
                    <a href="calendar.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                        <i class="fas fa-calendar-alt w-6"></i>
                        <span class="ml-3">Activity Calendar</span>
                    </a>
                    <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                        <i class="fas fa-user w-6"></i>
                        <span class="ml-3">Profile</span>
                    </a>
                    <a href="logout.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        <span class="ml-3">Logout</span>
                    </a>
                </nav>
            </div>
    <div class="flex-1 overflow-auto">
        <header class="bg-white dark:bg-gray-800 shadow flex items-center justify-between px-6 py-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Profile</h1>
            <div class="flex flex-col items-end">
                <?php
                $student_name = $_SESSION['first_name'] ?? '';
                $student_lname = $_SESSION['last_name'] ?? '';
                $reg_number = $_SESSION['reg_number'] ?? '';
                ?>
                <span class="font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($student_name . ' ' . $student_lname); ?></span>
                <span class="text-xs text-gray-500 dark:text-gray-300"><?php echo htmlspecialchars($reg_number); ?></span>
            </div>
            <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
            </button>
        </header>
        <script>
            // Dark mode toggle
            const darkModeToggle = document.getElementById('darkModeToggle');
            const html = document.documentElement;
            if (localStorage.getItem('darkMode') === 'true') {
                html.classList.add('dark');
            }
            darkModeToggle.addEventListener('click', () => {
                html.classList.toggle('dark');
                localStorage.setItem('darkMode', html.classList.contains('dark'));
            });
        </script>
        <main class="p-6">
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Profile updated successfully.</span>
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Profile Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Profile Information</h2>
                    <form method="post">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="name">Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="reg_number">Register Number</label>
                            <input type="text" id="reg_number" name="reg_number" value="<?php echo htmlspecialchars($student['reg_number']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" readonly>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="department">Department</label>
                            <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($student['department']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="academic_year">Academic Year</label>
                            <input type="text" id="academic_year" name="academic_year" value="<?php echo htmlspecialchars($student['academic_year']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="section">Section</label>
                            <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($student['section']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="dob">Date of Birth</label>
                            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($student['dob']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="gender">Gender</label>
                            <select id="gender" name="gender" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="male" <?php if($student['gender']==='male') echo 'selected'; ?>>Male</option>
                                <option value="female" <?php if($student['gender']==='female') echo 'selected'; ?>>Female</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="mobile">Mobile</label>
                            <input type="text" id="mobile" name="mobile" value="<?php echo htmlspecialchars($student['mobile']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="hostel_day">Hosteller/DayScholar</label>
                            <select id="hostel_day" name="hostel_day" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="hosteller" <?php if($student['hostel_day']==='hosteller') echo 'selected'; ?>>Hosteller</option>
                                <option value="dayscholar" <?php if($student['hostel_day']==='dayscholar') echo 'selected'; ?>>DayScholar</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($student['address']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mt-6 text-right">
                            <button type="submit" name="update_profile" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Save Changes</button>
                        </div>
                    </form>
                </div>
                <!-- Password Change (optional, not functional) -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Change Password</h2>
                    <form method="post" onsubmit="alert('Password change not implemented.'); return false;">
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 dark:text-gray-300 mb-2" for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mt-6 text-right">
                            <button type="submit" name="change_password" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html> 