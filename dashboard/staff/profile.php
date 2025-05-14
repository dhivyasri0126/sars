<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: ../../auth/staff_login.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "staff_signup";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get staff details
$staff_id = $_SESSION['staff_id'];
$sql = "SELECT * FROM staff WHERE id = $staff_id";
$result = $conn->query($sql);
$staff = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $department = $conn->real_escape_string($_POST['department']);
    $designation = $conn->real_escape_string($_POST['designation']);
    
    $sql = "UPDATE staff SET name = ?, email = ?, department = ?, designation = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $email, $department, $designation, $staff_id);
    
    if ($stmt->execute()) {
        // Update session variables
        $_SESSION['staff_name'] = $name;
        $_SESSION['staff_email'] = $email;
        $_SESSION['staff_department'] = $department;
        $_SESSION['staff_designation'] = $designation;
        
        header("Location: profile.php?success=1");
        exit();
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (password_verify($current_password, $staff['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE staff SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashed_password, $staff_id);
            
            if ($stmt->execute()) {
                header("Location: profile.php?password_success=1");
                exit();
            }
        } else {
            $password_error = "New passwords do not match";
        }
    } else {
        $password_error = "Current password is incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Staff Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4F46E5;
            --secondary-color: #818CF8;
            --dark-bg: #1F2937;
            --light-bg: #F3F4F6;
        }

        .dark-mode {
            --primary-color: #6366F1;
            --secondary-color: #A5B4FC;
            --dark-bg: #111827;
            --light-bg: #1F2937;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 bg-white dark:bg-gray-800 shadow-lg">
            <div class="p-4">
                <img src="../../assets/images/logo.png" alt="Logo" class="w-16 h-16 mx-auto">
                <h2 class="text-center text-xl font-bold mt-2 text-gray-800 dark:text-white">Staff Portal</h2>
            </div>
            <nav class="mt-6">
                <a href="dashboard.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-home w-6"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="students.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-3">Students</span>
                </a>
                <a href="activities.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-tasks w-6"></i>
                    <span class="ml-3">Activities</span>
                </a>
                <a href="approvals.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-check-circle w-6"></i>
                    <span class="ml-3">Approvals</span>
                </a>
                <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-user w-6"></i>
                    <span class="ml-3">Profile</span>
                </a>
                <a href="../../auth/logout.php" class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-indigo-100 dark:hover:bg-gray-700">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-3">Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Top Navigation -->
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="flex items-center justify-between px-6 py-4">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Profile</h1>
                    <div class="flex items-center space-x-4">
                        <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                            <i class="fas fa-moon dark:hidden"></i>
                            <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
                        </button>
                        <div class="relative">
                            <button class="flex items-center space-x-2">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($staff['name']); ?>" alt="Profile" class="w-8 h-8 rounded-full">
                                <span class="text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($staff['name']); ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="p-6">
                <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Profile updated successfully.</span>
                </div>
                <?php endif; ?>

                <?php if (isset($_GET['password_success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">Password changed successfully.</span>
                </div>
                <?php endif; ?>

                <?php if (isset($password_error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $password_error; ?></span>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Profile Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Profile Information</h2>
                        <form method="post">
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-gray-300 mb-2" for="name">Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($staff['name']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-gray-300 mb-2" for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($staff['email']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-gray-300 mb-2" for="department">Department</label>
                                <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($staff['department']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-gray-300 mb-2" for="designation">Designation</label>
                                <input type="text" id="designation" name="designation" value="<?php echo htmlspecialchars($staff['designation']); ?>" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <button type="submit" name="update_profile" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                Update Profile
                            </button>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Change Password</h2>
                        <form method="post">
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
                            <button type="submit" name="change_password" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        darkModeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
        });

        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>
</html>
<?php $conn->close(); ?> 