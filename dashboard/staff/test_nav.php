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

// Get current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Navigation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="p-4">
                <h2 class="text-center text-xl font-bold">Staff Portal</h2>
            </div>
            <nav class="mt-6">
                <a href="dashboard.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-100">
                    <i class="fas fa-home w-6"></i>
                    <span class="ml-3">Dashboard</span>
                </a>
                <a href="students.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-100">
                    <i class="fas fa-users w-6"></i>
                    <span class="ml-3">Students</span>
                </a>
                <a href="activities.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-100">
                    <i class="fas fa-tasks w-6"></i>
                    <span class="ml-3">Activities</span>
                </a>
                <a href="od_requests.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-100">
                    <i class="fas fa-clipboard-check w-6"></i>
                    <span class="ml-3">OD Requests</span>
                </a>
                <a href="approvals.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-100">
                    <i class="fas fa-check-circle w-6"></i>
                    <span class="ml-3">Approvals</span>
                </a>
                <a href="profile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-100">
                    <i class="fas fa-user w-6"></i>
                    <span class="ml-3">Profile</span>
                </a>
                <a href="../../auth/logout.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-100">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span class="ml-3">Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-2xl font-bold mb-4">Test Navigation</h1>
            <p>This is a test page to check if the navigation is working properly.</p>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?> 